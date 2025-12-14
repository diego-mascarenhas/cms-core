# User Roles and Permissions Update

## Implemented Changes

### 1. Created Policies

Two new policies were created:

- **`PostPolicy`**: Controls access to posts
  - All users can create posts
  - Admin can edit/delete any post
  - Members can only edit/delete their own posts

- **`UserPolicy`**: Controls access to user management
  - Only Admin can view, create, edit, and delete users
  - Members have no access to the users resource

### 2. Default Role Changed

- Changed from `guest` to `member` as the default role for new users
- Modified files:
  - `src/Filament/Resources/UserResource.php`
  - `src/Filament/Resources/UserResource/Pages/CreateUser.php`

### 3. Categories and Tags Restrictions

- **Categories**: Only Admin can create/edit categories in posts
- **Tags**: All users can create/edit tags
- Modified file: `src/Filament/Resources/PostResource.php`

### 4. Posts Filtering by User

- Admin sees all posts
- Members only see their own posts
- Modified file: `src/Filament/Resources/PostResource/Pages/ListPosts.php`

### 5. AuthServiceProvider Registered

- Created `app/Providers/AuthServiceProvider.php`
- Registered in `bootstrap/providers.php`

## Updating Roles in Existing Applications

For already installed applications, manually update roles in the database:

```sql
-- View users and their current roles
SELECT u.id, u.name, u.email, tu.role, tu.team_id
FROM users u
JOIN team_user tu ON tu.user_id = u.id;

-- Change from 'guest' to 'member' (if needed)
UPDATE team_user SET role = 'member' WHERE role = 'guest';

-- Assign admin to a specific user (replace the IDs)
UPDATE team_user SET role = 'admin' WHERE user_id = 1 AND team_id = 1;
```

## Verification

After updating roles:

1. Log out and log back in
2. Admin user should see the "Users" resource in the menu
3. When creating posts as admin, you should be able to add categories
4. Admin should be able to view and edit all system posts
5. Members will only see their own posts

## For New Installations

New package installations are already configured correctly:
- New users are created as 'member' by default
- Policies are automatically active
- No additional action required

## Permission Structure

### Admin Role
- ✅ Manage users (create, view, edit, delete)
- ✅ View all posts
- ✅ Edit/delete any post
- ✅ Create/edit categories
- ✅ Create/edit tags

### Member Role
- ❌ Cannot manage users
- ✅ View only their own posts
- ✅ Edit/delete only their own posts
- ❌ Cannot create/edit categories (can only view them)
- ✅ Create/edit tags

### Guest Role (deprecated)
- This role is no longer used by default
- Existing users with this role should be migrated to 'member'

## Modified Files

### Created Files
- `app/Policies/PostPolicy.php`
- `app/Policies/UserPolicy.php`
- `app/Providers/AuthServiceProvider.php`

### Modified Files
- `src/Filament/Resources/UserResource.php`
- `src/Filament/Resources/UserResource/Pages/CreateUser.php`
- `src/Filament/Resources/PostResource.php`
- `src/Filament/Resources/PostResource/Pages/ListPosts.php`
- `bootstrap/providers.php`

## Implementation Details

### UserPolicy Methods
- `viewAny()`: Only admins can access user list
- `view()`: Only admins can view user details
- `create()`: Only admins can create users
- `update()`: Only admins can update users
- `delete()`: Only admins can delete users

### PostPolicy Methods
- `viewAny()`: All users can access posts list (filtered by role)
- `view()`: All users can view posts
- `create()`: All users can create posts
- `update()`: Admin can update any post, members only their own
- `delete()`: Admin can delete any post, members only their own

### Category Management
The `canManageCategories()` method in `PostResource` checks if the current user is an admin:
- Admin: Can create new categories via SpatieTagsInput
- Members: Field is visible but disabled (read-only)

### Post Listing Filter
The `ListPosts` page implements `getTableQuery()` to filter posts:
- Admin: Returns all posts without filtering
- Members: Filters to show only posts where `user_id` matches current user

## Testing the Implementation

### As Admin
1. Log in as admin user
2. Navigate to Users - should see all users
3. Create a new user - should work
4. Navigate to Posts - should see all posts
5. Edit any post - should work
6. Create a new post - can add both tags and categories

### As Member
1. Log in as member user
2. Users menu item - should not be visible
3. Navigate to Posts - should only see own posts
4. Edit own post - should work
5. Try to edit another user's post - should get 403 error
6. Create a new post - can add tags but categories field is read-only
