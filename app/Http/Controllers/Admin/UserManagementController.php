<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\User;
use App\Services\AdminUserManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    protected string $pageTitle;

    public function __construct(protected AdminUserManagementService $userManagementService)
    {
        $this->pageTitle = 'User Management';
    }

    public function index(Request $request): View
    {
        $users = $this->userManagementService->getUsers([
            'search' => $request->input('search'),
            'role' => $request->input('role'),
        ]);

        return view('backend_panel_view_admin.pages.users.index', [
            'users' => $users,
            'page_title' => $this->pageTitle,
            'page_header' => 'Users',
            'roles' => [User::ADMIN, User::CUSTOMER],
        ]);
    }

    public function show($id): View
    {
        $user = User::findOrFail($id);

        return view('backend_panel_view_admin.pages.users.show', [
            'user' => $user,
            'page_title' => $this->pageTitle,
            'page_header' => 'User Details',
            'roles' => [User::ADMIN, User::CUSTOMER],
        ]);
    }

    public function updateRole(UpdateUserRoleRequest $request, $id): RedirectResponse
    {
        $targetUser = User::findOrFail($id);
        $result = $this->userManagementService->updateUserRole(
            $request->user(),
            $targetUser,
            $request->validated()['user_type']
        );

        if (!$result['success']) {
            return redirect()->back()->withErrors([
                'message' => $result['message'],
            ]);
        }

        return redirect()
            ->route('admin.users.show', $targetUser->id)
            ->with('success', $result['message']);
    }
}
