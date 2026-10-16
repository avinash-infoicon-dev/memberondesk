<x-app-layout heading="Users">
    <div class="card"><div class="table-wrap"><table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Business</th><th>Status</th><th>App access</th><th></th></tr></thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->role->label() }}</td>
                <td>{{ $user->business?->name ?? '—' }}</td>
                <td>{{ $user->is_active ? 'Active' : 'Disabled' }}</td>
                <td>
                    <span>{{ $user->can_access_app ? 'Allowed' : 'Blocked' }}</span>
                    <form method="POST" action="{{ route('super-admin.users.app-access', $user) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="can_access_app" value="{{ $user->can_access_app ? '0' : '1' }}">
                        <button class="btn btn-ghost btn-sm" type="submit">{{ $user->can_access_app ? 'Block app access' : 'Allow app access' }}</button>
                    </form>
                </td>
                <td>
                    <form method="POST" action="{{ route('super-admin.users.toggle', $user) }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-ghost btn-sm" type="submit">Toggle</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table></div><div class="pager">{{ $users->links() }}</div></div>
</x-app-layout>
