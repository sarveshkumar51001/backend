@extends('app')

@section('header')
    @include('admin.common.header')
@endsection

@section('body')
    <div class="sidebar">
        @include('admin.common.sidebar')
    </div>
    <main class="main">
        <!-- Breadcrumb -->
        <ol class="breadcrumb">
            @include('admin.common.breadcrumb')
        </ol>

        <!-- Main body contetns -->
        <div class="container-fluid">
            <div class="animated">
                @yield('content')
            </div>
        </div>
    </main>
    <aside class="aside-menu" style="overflow-y: auto;">
        @yield('aside-content')
    </aside>
@endsection

@section('footer')
    @include('admin.common.footer')
@endsection