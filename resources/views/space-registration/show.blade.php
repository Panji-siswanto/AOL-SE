<x-user-layout>
    <div class="space-registration-show">
                <h1>{{ $spaceRegistration->name }}</h1>
                <p>{{ $spaceRegistration->description }}</p>
                <p>Location: {{ $spaceRegistration->location }}</p>
                <p>Capacity: {{ $spaceRegistration->capacity }}</p>
                <p>Status: {{ $spaceRegistration->status }}</p>
    </div>
</x-user-layout>