@extends('layouts.admin')

@section('title', 'Proses Bantuan Kebencanaan - SOLID Dinas Sosial Kota Bogor')
@section('page_title', 'Proses Bantuan Kebencanaan')

@section('content')
    @include('admin.sigap-bencana._daftar', [
        'rutaDaftar' => 'sigap.proses.index',
        'tampilGiliran' => false,
        'prosesMode' => true,
    ])
@endsection
