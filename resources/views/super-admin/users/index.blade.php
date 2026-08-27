<x-app-layout heading="Users">
    <div class="card"><div class="table-wrap"><table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Business</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->role->label() }}</td>
                <td>{{ $user->business?->name ?? '—' }}</td>
                <td>{{ $user->is_active ? 'Active' : 'Disabled' }}</td>
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
