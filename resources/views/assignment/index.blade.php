@extends('layouts.auth')
@section('title', 'Assignment Management')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
@endpush

@section('content')
    <div class="container-fluid">
        @include('layouts.components.breadcrumb')

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="fas fa-tasks me-2"></i>Daftar Tugas</h5>
                {{-- Tombol ini mengarah ke halaman create, bukan membuka modal --}}
                <a href="{{ url('/assignment/create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Tugas Baru
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    {{-- PERUBAHAN UTAMA: Menambahkan data-edit-url untuk dibaca oleh JavaScript --}}
                    <table id="assignment-datatable"
                           class="table table-bordered table-striped table-hover dt-responsive nowrap" style="width:100%"
                           data-edit-url="{{ url('/assignments') }}">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Judul</th>
                                <th>Mata Pelajaran</th>
                                <th>Kelas</th>
                                <th>Tipe</th>
                                <th>Batas Waktu</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Konten diisi oleh DataTables secara dinamis --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Library yang dibutuhkan --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Pastikan path ke file JS ini sudah benar --}}
    <script src="{{ asset('assets/js/app/assignment/assignment.js') }}"></script>
@endpush
