<x-app-layout heading="App settings">
    <div class="card"><div class="card-b">
        <form method="POST" action="{{ route('super-admin.settings.update') }}" class="form">
            @csrf @method('PUT')
            <label>App lock key
                <input name="app_lock_key" value="{{ old('app_lock_key', $appLockKey) }}" maxlength="255" autocomplete="off">
            </label>
            @error('app_lock_key') <p role="alert">{{ $message }}</p> @enderror
            <p>The dashboard API returns this key to the mobile app. Leave blank to disable the key prompt. Changing the key applies to all businesses on their next dashboard request.</p>
            <button class="btn btn-primary" type="submit">Save settings</button>
        </form>
    </div></div>
</x-app-layout>
