@extends('layouts')

@section('content')
</br>
<div class="container">
    <h2>Login</h2>
    <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
    </br>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>
@endsection
