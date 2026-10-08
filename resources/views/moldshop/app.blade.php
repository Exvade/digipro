@extends('layouts.app')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/moldshop.js') }}?v={{ filemtime(public_path('js/moldshop.js')) }}"></script>
@endpush