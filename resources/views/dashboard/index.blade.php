@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="dashboard-header">
    <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">
            Selamat datang kembali, {{ Auth::user()->name }} 👋
        </p>
    </div>
</div>
@endsection