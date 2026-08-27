<x-app-layout heading="Register member">
    <div class="card"><div class="card-b">
        <form method="POST" action="{{ route('business.members.store') }}" class="form">
            @csrf
            @include('business.members._form')
            <button class="btn btn-primary" type="submit">Save member</button>
        </form>
    </div></div>
</x-app-layout>
