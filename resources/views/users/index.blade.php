@extends('layouts.reporting', ['title' => 'Users'])

@section('content')
    <section class="hero">
        <div class="hero-label">Administration</div>
        <h1>Users</h1>
        <p>Add reporting users and manage their roles.</p>
    </section>

    @if($errors->any())
        <div class="auth-error">{{ $errors->first() }}</div>
    @endif

    <div class="grid-main">
        <div class="card">
            <h2 class="section-title">All users</h2>

            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>
                            <form method="POST" action="{{ route('users.role', $u) }}">
                                @csrf
                                @method('PUT')
                                <select name="role" onchange="this.form.submit()">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->value }}" @selected($u->role === $role)>{{ $role->label() }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </td>
                        <td class="money">
                            @if(! $u->is(auth()->user()))
                                <form method="POST" action="{{ route('users.destroy', $u) }}" class="inline-form"
                                      onsubmit="return confirm('Remove {{ $u->email }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="button danger">Remove</button>
                                </form>
                            @else
                                <span class="muted">You</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2 class="section-title">Add user</h2>

            <form method="POST" action="{{ route('users.store') }}">
                @csrf

                <label for="name">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required>

                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>

                <label for="role">Role</label>
                <select id="role" name="role">
                    @foreach($roles as $role)
                        <option value="{{ $role->value }}" @selected(old('role', 'manager') === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>

                <p class="field-help">
                    They'll receive an email to set their own password before they can sign in.
                </p>

                <button type="submit" class="button auth-submit">Send invitation</button>
            </form>
        </div>
    </div>
@endsection
