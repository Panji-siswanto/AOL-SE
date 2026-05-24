<x-user-layout>
    <div class="min-h-screen bg-slate-50 py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="rounded-[2rem] ">
                <div class="max-w-3xl">
                    <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Tambah Lahan Baru</h1>
                    <p class="mt-3 text-base text-slate-500">Isi detail lahan yang ingin Anda sewakan.</p>
                </div>
            </div>

            <form action="{{ route('space-registrations.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf

                @if ($errors->any())
                    <div class="rounded-3xl border border-red-200/70 bg-red-50 px-6 py-4 text-sm text-red-700 shadow-sm">
                        <div class="font-semibold">Terdapat beberapa kesalahan:</div>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="rounded-[2rem] border border-gray-200/70 bg-white p-8 shadow-sm space-y-8">
                    <div class="space-y-3">
                        <label for="name" class="text-sm font-medium text-slate-700">Nama Lahan</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Contoh: Ruko Jl. Sudirman"
                            class="block w-full rounded-3xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100" />
                    </div>

                    <div class="space-y-6 rounded-3xl border border-gray-200 bg-slate-50 p-6">
                        <div class="text-sm font-semibold text-slate-900">Lokasi</div>
                        <div class="grid gap-4 lg:grid-cols-[1fr_auto]">
                            <div class="space-y-4">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-3">
                                    <!-- <label for="address" class="text-sm font-medium text-slate-700">Alamat</label> -->
                                    <input id="address" name="address" type="text" value="{{ old('address') }}" placeholder="Masukkan alamat lengkap"
                                        class="block w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100" />
                                </div>
 
                            </div>

                            <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-6 shadow-sm min-h-[240px] flex items-center justify-center">
                                <div class="text-center text-slate-400">
                                    <div class="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-2xl">🗺️</div>
                                    <p class="text-sm font-medium">Map Placeholder</p>
                                    <!-- <p class="mt-2 text-sm text-slate-500">Lokasi akan ditampilkan di sini.</p> -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 lg:grid-cols-2">
                        <div class="space-y-3">
                            <label for="size" class="text-sm font-medium text-slate-700">Ukuran</label>
                            <input id="size" name="size" type="text" value="{{ old('size') }}" placeholder="Contoh: 50 m²"
                                class="block w-full rounded-3xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100" />
                        </div>
                        <div class="space-y-3">
                            <label for="price" class="text-sm font-medium text-slate-700">Harga Sewa (per bulan)</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">Rp</span>
                                <input id="price" name="price" type="number" value="{{ old('price') }}" placeholder="Contoh: 5000000"
                                    class="block w-full rounded-3xl border border-gray-200 bg-gray-50 px-12 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100" />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label for="category" class="text-sm font-medium text-slate-700">Kategori</label>
                        <select id="category" name="category"
                            class="block w-full rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100">
                            <option value="">Pilih kategori</option>
                            <option value="ruko" {{ old('category') == 'ruko' ? 'selected' : '' }}>Ruko</option>
                            <option value="kios" {{ old('category') == 'kios' ? 'selected' : '' }}>Kios</option>
                            <option value="gudang" {{ old('category') == 'gudang' ? 'selected' : '' }}>Gudang</option>
                            <option value="outdoor" {{ old('category') == 'outdoor' ? 'selected' : '' }}>Lapak Outdoor</option>
                        </select>
                    </div>

                    <div class="space-y-4">
                        <div class="text-sm font-semibold text-slate-900">Fasilitas</div>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            @php
                                $facilities = ['Parkir','Toilet','Listrik','Air','Wi-Fi','AC','CCTV','Keamanan 24 Jam'];
                            @endphp
                            @foreach ($facilities as $facility)
                                <label class="inline-flex cursor-pointer items-center rounded-3xl border border-gray-200 bg-white px-4 py-3 text-sm text-slate-700 transition hover:border-teal-300">
                                    <input type="checkbox" name="facilities[]" value="{{ $facility }}" class="mr-3 h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500" {{ in_array($facility, old('facilities', [])) ? 'checked' : '' }}>
                                    {{ $facility }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="space-y-3">
                        <label for="description" class="text-sm font-medium text-slate-700">Deskripsi</label>
                        <textarea id="description" name="description" rows="5" placeholder="Jelaskan detail lahan, akses lokasi, dan informasi penting lainnya..."
                            class="block w-full rounded-3xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-teal-500 focus:ring-2 focus:ring-teal-100">{{ old('description') }}</textarea>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">Upload Foto</div>
                            </div>
                        </div>
                        <label for="photos" class="group block cursor-pointer rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center transition hover:border-teal-400 hover:bg-slate-100">
                            <div class="mx-auto mb-4 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-50 text-teal-600">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                                    <path fill-rule="evenodd" d="M12 5.25a.75.75 0 01.75.75v5.19l2.72-2.72a.75.75 0 111.06 1.06l-4 4a.75.75 0 01-1.06 0l-4-4a.75.75 0 111.06-1.06l2.72 2.72V6a.75.75 0 01.75-.75z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="space-y-2">
                                <p class="text-sm font-semibold text-slate-900">
                                    <span class="text-green-500 semibold">
                                        Klik untuk upload
                                    </span> 
                                    atau drag and drop
                                </p>
                                <p class="text-sm text-slate-500">PNG, JPG hingga 10MB</p>
                            </div>
                            <input id="photos" name="photos[]" type="file" accept="image/png,image/jpeg" multiple class="hidden" />
                        </label>
                    </div>

                    <div class="flex gap-3 flex-row ">
                        <button type="submit" class="inline-flex items-center justify-center rounded-3xl bg-teal-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-700">Simpan Lahan</button>    
                        <a class="inline-flex items-center justify-center rounded-3xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Batal</a>     
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-user-layout>