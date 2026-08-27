<x-app-layout heading="New plan">
    <div class="card"><div class="card-b">
        <form method="POST" action="{{ route('business.plans.store') }}" class="form">
            @csrf
            @include('business.plans._form')
            <button class="btn btn-primary" type="submit">Create plan</button>
        </form>
    </div></div>
</x-app-layout>
