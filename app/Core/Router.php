<?php

declare(strict_types=1);

namespace EduSphere\Core;

class Router
{
    private array $routes = [];
    private array $groupStack = [];

    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    public function get(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    private function addRoute(string $method, string $path, $handler, array $middlewares): void
    {
        $prefix = '';
        $groupMiddlewares = [];

        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (isset($group['middleware'])) {
                $m = is_array($group['middleware']) ? $group['middleware'] : [$group['middleware']];
                $groupMiddlewares = array_merge($groupMiddlewares, $m);
            }
        }

        $fullPath = '/' . trim($prefix . '/' . trim($path, '/'), '/');
        if ($fullPath === '//') {
            $fullPath = '/';
        }

        $allMiddlewares = array_merge($groupMiddlewares, $middlewares);

        // Convert path to regex for parameters like {id} or {slug}
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $fullPath);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'pattern' => $pattern,
            'handler' => $handler,
            'middlewares' => $allMiddlewares,
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                // Filter string keys for parameters
                $params = [];
                foreach ($matches as $key => $val) {
                    if (is_string($key)) {
                        $params[$key] = $val;
                    }
                }

                // Check CSRF on POST
                if ($method === 'POST') {
                    $token = $request->post('_csrf') ?? $request->post('_token') ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
                    if (!Csrf::validate($token)) {
                        http_response_code(419);
                        die("CSRF Token Expired or Invalid. Please refresh the page and try again.");
                    }
                }

                // Run middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $this->runMiddleware($middleware, $request);
                }

                // Execute handler
                $positionalArgs = array_merge([$request], array_values($params));

                $handler = $route['handler'];
                if (is_callable($handler)) {
                    call_user_func_array($handler, $positionalArgs);
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$class, $action] = $handler;
                    $controller = new $class();
                    call_user_func_array([$controller, $action], $positionalArgs);
                    return;
                }

                http_response_code(500);
                die("Invalid route handler configuration.");
            }
        }

        http_response_code(404);
        View::render('errors.404', ['path' => $path], 'auth');
    }

    private function runMiddleware(string $middleware, Request $request): void
    {
        // 1. Auth middleware
        if ($middleware === 'auth') {
            if (!Auth::check()) {
                Response::redirect('/login', 'Please sign in to access this page.', 'warning');
            }
            return;
        }

        // 2. Guest middleware
        if ($middleware === 'guest') {
            if (Auth::check()) {
                $role = Auth::role();
                if ($role === 'super_admin') {
                    Response::redirect('/superadmin');
                } elseif (in_array($role, ['sales_rep', 'sales_manager'])) {
                    Response::redirect('/sales');
                } elseif ($role === 'student') {
                    Response::redirect('/portal/student');
                } elseif ($role === 'parent') {
                    Response::redirect('/portal/parent');
                } else {
                    Response::redirect('/school');
                }
            }
            return;
        }

        // 3. Role middleware: role:super_admin or role:school_admin,principal
        if (str_starts_with($middleware, 'role:')) {
            if (!Auth::check()) {
                Response::redirect('/login', 'Access denied. Please log in.', 'danger');
            }

            $allowedRoles = explode(',', substr($middleware, 5));
            $userRole = Auth::role();

            if (!in_array($userRole, $allowedRoles, true)) {
                http_response_code(403);
                View::render('errors.403', ['message' => 'You do not have permission to access this area.'], 'auth');
                exit;
            }
            return;
        }

        // 4. Tenant subscription active check
        if ($middleware === 'tenant_active') {
            if (!TenantContext::checkSubscriptionActive()) {
                Response::redirect('/subscription-expired', 'School subscription has expired or is currently suspended. Please contact the platform administrator.', 'danger');
            }
            return;
        }
    }
}
