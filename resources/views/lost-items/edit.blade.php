<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Edit Lost Item Report</h1>
    </x-slot>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('lost-items.update', $lostItem) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @include('lost-items._form')

                <x-primary-button>Save Changes</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
