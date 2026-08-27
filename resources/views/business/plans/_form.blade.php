<label>Name <input name="name" value="{{ old('name', $plan->name ?? '') }}" required></label>
<label>Description <textarea name="description" rows="3">{{ old('description', $plan->description ?? '') }}</textarea></label>
<div class="form-row">
    <label>Duration (days) <input type="number" name="duration_days" min="1" value="{{ old('duration_days', $plan->duration_days ?? 30) }}" required></label>
    <label>Price (INR) <input type="number" step="0.01" name="price" value="{{ old('price', $plan->price ?? '') }}" required></label>
</div>
<label style="display:flex;gap:8px;align-items:center;font-weight:500">
    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active ?? true)) style="width:auto"> Active
</label>
