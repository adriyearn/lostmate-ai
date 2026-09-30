<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Report a Found Item</h1>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('found-items.store') }}" enctype="multipart/form-data">
                @csrf

                @include('found-items._form')

                <x-primary-button>Submit Report</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
