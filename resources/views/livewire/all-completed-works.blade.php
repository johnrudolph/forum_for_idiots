<div wire:poll>
    <div class="mt-8 flex flex-col gap-y-4">
        <div>
            <div class="flow-root mt-6">
                <div>
                    <p class="text-white text-lg font-semibold">
                        All Posts
                    </p>
                </div>
                <div class="flex flex-col-3 py-4 space-x-5">
                    <p class="text-white text-sm font-semibold">Sort by:</p>
                    <button wire:click="sortByScore">
                        <p class="text-blue-500 text-sm font-semibold">
                            Top
                        </p>
                    </button>
                    <button wire:click="sortByRecent">
                        <p class="text-blue-500 text-sm font-semibold">
                            Recent
                        </p>
                    </button>
                </div>
                <div class="flex flex-col-3 pb-5 space-x-5">
                    <p class="text-white text-sm font-semibold">Filter by:</p>
                    <button wire:click="filter('all')">
                        <p class="text-blue-500 text-sm font-semibold">
                            All
                        </p>
                    </button>
                    <button wire:click="filter('advice')">
                        <p class="text-blue-500 text-sm font-semibold">
                            Advice
                        </p>
                    </button>
                    <button wire:click="filter('word')">
                        <p class="text-blue-500 text-sm font-semibold">
                            Word of the Day
                        </p>
                    </button>
                </div>
                <ul role="list" class="-my-4 divide-y divide-gray-200">
                    @foreach($works as $work)
                        <li class="py-5">
                            <div class="relative">
                                <h3 class="text-md font-semibold text-white">
                                    {{ $work->title }}
                                </h3>
                            </div>
                            <div class="relative py-2">
                                <h3 class="text-sm text-white">
                                    {{ $submissions->where('work_id', $work->id)->first()->text }}
                                </h3>
                            </div>
                            <div class="flex flex-col-3 items-center gap-x-2">
                                <div class="pt-2 flex flex-col-3 gap-x-2">
                                    <div wire:click="upvoteWork( {{ $work->id }} )">
                                        <x-icons.upvote/>
                                    </div>
                                    <div>
                                        <p class="text-white">
                                            {{ $work->score }}
                                        </p>
                                    </div>
                                    <div wire:click="downvoteWork( {{ $work->id }} )">
                                        <x-icons.downvote/>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-white text-sm">
                                        {{ $work->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <div class="relative py-2">
                                <h3 class="text-xs italic text-white">
                                    @if($work->type === 'word_of_the_day')
                                        Word submitted by {{$work->user->name}}. Definition submitted by {{ $submissions->where('work_id', $work->id)->first()->user->name }}.
                                    @elseif($work->type === 'advice')
                                        Question submitted by {{$work->user->name}}. Advice submitted by {{ $submissions->where('work_id', $work->id)->first()->user->name }}.
                                    @endif
                                </h3>
                            </div>
                            @if($user->is_moderator)
                                <div>
                                    <button wire:click="delete( {{$work->id}} )">
                                        <p class="text-red-500 text-sm">
                                            Delete
                                        </p>
                                    </button>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
