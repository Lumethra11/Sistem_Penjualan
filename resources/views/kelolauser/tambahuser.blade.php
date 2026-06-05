@extends('layouts.app')

@section('title', 'Tambah User')

@vite('resources/css/pages/kelolauser.css')

@section('content')

<div class="kelola-user-container">

    <h1>Kelola User</h1>

    <div class="tab-container">

        <a href="{{ route('kelolauser.tambah') }}"
           class="tab-button active">
            Tambah User
        </a>

        <a href="{{ route('kelolauser.daftar') }}"
           class="tab-button">
            Daftar User
        </a>

    </div>


    <div class="form-container">

        <form action="{{ route('kelolauser.store') }}" method="POST">

            @csrf

            <div class="form-row">

                <div class="form-group">
                    <label>Nama Lengkap</label>

                    <div class="input-wrapper">
                        <input type="text" name="name" required>
                    </div>
                </div>


                <div class="form-group">
                    <label>Username</label>

                    <div class="input-wrapper">
                        <input type="text" name="username" required>
                    </div>
                </div>

            </div>


            <div class="form-row">

                <div class="form-group">
                    <label>Email</label>

                    <div class="input-wrapper">
                        <input type="email" name="email" required>
                    </div>
                </div>


                <div class="form-group">
                    <label>No Telepon</label>

                    <div class="input-wrapper">
                        <input type="text" name="no_telp">
                    </div>
                </div>

            </div>


            <div class="form-group">
                <label>Alamat Lengkap</label>

                <div class="input-wrapper">
                    <textarea name="alamat"></textarea>
                </div>
            </div>


            <div class="form-row">

                <div class="form-group">
                    <label>Password</label>

                    <div class="input-wrapper">
                        <input type="password" name="password" required>
                    </div>
                </div>


                <div class="form-group">
                    <label>Konfirmasi Password</label>

                    <div class="input-wrapper">
                        <input type="password"
                               name="password_confirmation"
                               required>
                    </div>
                </div>

            </div>

            <button type="submit" class="submit-btn">
                Tambah User
            </button>

        </form>

    </div>

</div>

@endsection