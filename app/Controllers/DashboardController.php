<?php
// app/Controllers/DashboardController.php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\{CSRF, Session, Validator};
use App\Middleware\Auth;
use App\Models\{UserModel, ServiceModel, SwapModel};

class DashboardController
{
    // GET /dashboard
    public function index(): void
    {
        Auth::requireLogin();
        $users    = new UserModel();
        $swapMdl  = new SwapModel();

        $user       = $users->findById(Auth::id());
        $myServices = (new ServiceModel())->getByUser(Auth::id());
        $mySwaps    = $swapMdl->getUserSwaps(Auth::id());

        require APP_ROOT . '/app/Views/dashboard/index.php';
    }

    // GET /profile
    public function profile(): void
    {
        Auth::requireLogin();
        $users  = new UserModel();
        $user   = $users->getPublicProfile(Auth::id());
        $error  = Session::getFlash('error');
        $success = Session::getFlash('success');
        require APP_ROOT . '/app/Views/dashboard/profile.php';
    }

    // POST /profile/update
    public function updateProfile(): void
    {
        Auth::requireLogin();
        try { CSRF::verify($_POST['_csrf_token'] ?? ''); }
        catch (\RuntimeException) {
            Session::flash('error', 'Security token invalid.');
            header('Location: ' . APP_BASE . '/profile');
            exit;
        }

        $allowed = ['available', 'limited', 'unavailable'];

        $v = new Validator($_POST);
        $v->required('full_name')->min('full_name', 2)->max('full_name', 100)
          ->max('bio', 500)
          ->max('skills', 300)
          ->in('availability', $allowed);

        if ($v->fails()) {
            Session::flash('error', array_values($v->errors())[0]);
            header('Location: ' . APP_BASE . '/profile');
            exit;
        }

        (new UserModel())->updateProfile(Auth::id(), [
            'full_name'    => $v->get('full_name'),
            'bio'          => $v->get('bio'),
            'skills'       => $v->get('skills'),
            'availability' => $v->get('availability'),
        ]);

        // Update session name
        Session::set('user_name', $v->get('full_name'));
        Session::flash('success', 'Profile updated successfully.');
        header('Location: ' . APP_BASE . '/profile');
        exit;
    }

    // GET /users/:id
    public function viewUser(array $params): void
    {
        $users   = new UserModel();
        $profile = $users->getPublicProfile((int)$params['id']);
        if (!$profile) {
            http_response_code(404);
            require APP_ROOT . '/public/404.php';
            return;
        }
        $services = (new ServiceModel())->getByUser((int)$params['id']);
        $reviews  = (new \App\Models\ReviewModel())->getForUser((int)$params['id']);
        require APP_ROOT . '/app/Views/users/profile.php';
    }

    // GET /subscriptions
    public function subscriptions(): void
    {
        require APP_ROOT . '/app/Views/subscriptions/index.php';
    }
}
