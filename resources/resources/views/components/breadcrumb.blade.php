@props(['items' => []])

<nav class="flex mb-3" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1.5 md:space-x-2 text-xs font-semibold text-gray-900 dark:text-white">
        <!-- Home Link -->
        <li class="inline-flex items-center">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-gray-900 dark:text-white hover:underline transition">
                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-900 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Home
            </a>
        </li>

        @foreach($items as $item)
            <li>
                <div class="flex items-center">
                    <svg class="w-3.5 h-3.5 text-gray-900 dark:text-white mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    @if(isset($item['url']) && !$loop->last)
                        <a href="{{ $item['url'] }}" class="ml-1 text-gray-900 dark:text-white hover:underline transition">
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="ml-1 text-gray-900 dark:text-white font-bold">
                            {{ $item['label'] ?? $item }}
                        </span>
                    @endif
                </div>
            </li>
        @endforeach
    </ol>
</nav>
