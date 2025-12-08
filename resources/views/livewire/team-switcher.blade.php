<div class="py-1">
    <div class="px-4 py-2 text-xs text-gray-400 uppercase tracking-wider">
        {{ __('Teams') }}
    </div>
    @foreach($this->teams as $team)
        <button
            wire:click="switchTeam({{ $team->id }})"
            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
        >
            {{-- Green check only for active team --}}
            @if($team->id === $this->currentTeamId)
                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
            @else
                {{-- Empty space to maintain alignment --}}
                <span class="w-4 h-4 flex-shrink-0"></span>
            @endif
            <span class="truncate">{{ $team->name }}</span>
        </button>
    @endforeach
</div>
