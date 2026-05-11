@props([
    'target'
])

<button
    type="button"
    onclick="togglePassword('{{ $target }}')"
    class="absolute inset-y-0 right-0 flex items-center pr-5 text-gray-500 hover:text-gray-700"
>

    {{-- Eye Open --}}
    <svg
        id="{{ $target }}-eye-open"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="currentColor"
        class="w-6 h-6"
    >
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />

        <path stroke-linecap="round" stroke-linejoin="round"
            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>

    {{-- Eye Closed --}}
    <svg
        id="{{ $target }}-eye-closed"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="currentColor"
        class="hidden w-6 h-6"
    >
        <path stroke-linecap="round" stroke-linejoin="round"
            d="M3 3l18 18" />

        <path stroke-linecap="round" stroke-linejoin="round"
            d="M10.477 10.488A3 3 0 0012 15a3 3 0 002.523-4.512" />

        <path stroke-linecap="round" stroke-linejoin="round"
            d="M6.228 6.228C4.18 7.651 2.686 9.693 2.036 11.683a1.012 1.012 0 000 .639C3.423 16.49 7.36 19.5 12 19.5c2.136 0 4.119-.64 5.772-1.728M9.88 4.68A10.953 10.953 0 0112 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639a11.042 11.042 0 01-4.293 5.143" />
    </svg>

</button>

<script>
    function togglePassword(id) {

        const passwordInput = document.getElementById(id);
        const eyeOpen = document.getElementById(id + '-eye-open');
        const eyeClosed = document.getElementById(id + '-eye-closed');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';

            eyeOpen.classList.add('hidden');
            eyeClosed.classList.remove('hidden');

        } else {
            passwordInput.type = 'password';

            eyeOpen.classList.remove('hidden');
            eyeClosed.classList.add('hidden');
        }
    }
</script>