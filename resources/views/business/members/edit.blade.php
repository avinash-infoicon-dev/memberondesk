<x-app-layout heading="Edit member">
    <div class="card"><div class="card-b">
        <form method="POST" action="{{ route('business.members.update', $member) }}" class="form">
            @csrf @method('PUT')
            @include('business.members._form', ['member' => $member])
            <button class="btn btn-primary" type="submit">Update member</button>
        </form>
    </div></div>
</x-app-layout>
