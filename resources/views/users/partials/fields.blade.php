<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Nama</label>
        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label for="password" class="form-label">Password</label>
        <input type="password" id="password" name="password" class="form-control" @required(! $user->exists)>
    </div>
    <div class="col-md-6">
        <label for="roles" class="form-label">Role</label>
        <select id="roles" name="roles[]" class="form-select" multiple>
            @foreach ($roles as $role)
                <option value="{{ $role->name }}" @selected(collect(old('roles', $user->roles->pluck('name')->all()))->contains($role->name))>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>
    </div>
</div>
