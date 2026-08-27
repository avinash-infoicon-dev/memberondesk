<x-app-layout heading="Edit plan">
    <div class="card"><div class="card-b">
        <form method="POST" action="{{ route('business.plans.update', $plan) }}" class="form">
            @csrf @method('PUT')
            @include('business.plans._form', ['plan' => $plan])
            <button class="btn btn-primary" type="submit">Save plan</button>
        </form>
    </div></div>
</x-app-layout>
