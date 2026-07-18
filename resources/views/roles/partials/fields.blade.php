<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Nama Role</label>
        <input type="text" id="name" name="name" value="{{ old('name', $role->name) }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label for="permissions" class="form-label">Permission</label>
        <select id="permissions" name="permissions[]" class="form-select" multiple>
            @foreach ($groupedPermissions as $module => $permissions)
                <optgroup label="{{ $module }}">
                    @foreach ($permissions as $permission => $label)
                        <option value="{{ $permission }}" @selected(collect(old('permissions', $role->permissions->pluck('name')->all()))->contains($permission))>
                            {{ $label }}
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
    </div>
</div>
