@extends('layouts.auth')

@section('title', 'All Assignment Submissions')

@section('content')
    <div class="container-fluid">
        {{-- Breadcrumb --}}
        @include('layouts.components.breadcrumb')

        {{-- Main Content --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Submission List</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            {{-- Include table component --}}
                            @include('assignment-submission.components.table-submission')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Include modal component (kita bisa pakai ulang modal sebelumnya) --}}
    @include('assignment-submission.components.modal-submission')
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/app/assignment/submission.js') }}"></script>
@endpush
