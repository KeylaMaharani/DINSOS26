@extends('layouts.admin')

@section('title', 'Laporan Bantuan Bencana - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'SIGAP Bencana — Laporan Bantuan Bencana')

@section('content')
    @include('admin.sigap-bencana._daftar', [
        'rutaDaftar' => 'sigap.index',
        'tampilGiliran' => true,
        'prosesMode' => false,
    ])
@endsection
