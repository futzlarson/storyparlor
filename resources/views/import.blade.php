@extends('layouts.app')

@push('styles')
    input[type="checkbox"] {
        transform: scale(1.5);
    }
    input[type="text"] {
        min-width: 300px;
    }
@endpush

@section('content')

<div class="p-5">
    <h1>Import Customers</h1>

    @if ($errors->any())
        <div class="alert alert-danger d-inline-block">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success d-inline-block">
            {{ session('success') }}
        </div>
    @endif

    <p>This will import all orders into the database for the purpose of flagging first-time customers.</p>

    <form x-data="{ fileSelected: false, processing: false }"
        @submit="processing = true"
        action="import"
        class="mt-2 d-inline-block"
        enctype="multipart/form-data"
        method="post">
        @csrf
        <input @change="fileSelected = $event.target.files.length > 0" 
            class="form-control form-control-lg mb-4"
            name="file"
            type="file">

        <button :disabled="!fileSelected || processing" 
            x-text="processing ? 'Processing...' : 'Process'"
            :disabled="disabled"
            class="btn btn-primary">Process</button>
    </form>
</div>

@endsection