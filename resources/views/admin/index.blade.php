<x-admin-layout>
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-black text-gray-900 mb-2">Welcome, {{ $Admin->name }}</h1>
        <p class="text-gray-500 text-sm mb-8">System overview and core moderation controls for Lapak.in marketplace operations.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-200/80 shadow-sm flex flex-col justify-between transition-all hover:shadow-md">
                <div>
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 bg-orange-50 text-orange-600 rounded-2xl flex items-center justify-center font-bold text-xl">
                            🛡️
                        </div>
                        <span class="text-4xl font-black text-gray-900">{{ $pendingVerifications ?? 0 }}</span>
                    </div>
                    <h3 class="font-bold text-lg text-gray-900">Identity Verification Queue</h3>
                    <p class="text-xs text-gray-500 mt-1">Pending KTP documents and user selfies requiring manual document review and vetting.</p>
                </div>
                <a href="{{ route('admin.user-verifications.index') }}" class="mt-6 inline-block text-center bg-gray-900 hover:bg-gray-800 text-white font-bold py-3 rounded-xl transition">
                    Review Verification Queue
                </a>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-200/80 shadow-sm flex flex-col justify-between transition-all hover:shadow-md">
                <div>
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center font-bold text-xl">
                            🏪
                        </div>
                        <span class="text-4xl font-black text-gray-900">{{ $pendingListings ?? 0 }}</span>
                    </div>
                    <h3 class="font-bold text-lg text-gray-900">New Space Applications</h3>
                    <p class="text-xs text-gray-500 mt-1">Submitted commercial booth spots and space registers requiring platform publishing clearance.</p>
                </div>
                <a href="{{ route('admin.listing-requests.index') }}" class="mt-6 inline-block text-center bg-teal-600 hover:bg-teal-700 text-white font-bold py-3 rounded-xl transition">
                    Manage Space Listings
                </a>
            </div>

        </div>
    </div>
</x-admin-layout>