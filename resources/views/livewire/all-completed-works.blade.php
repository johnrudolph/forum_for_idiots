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
                    <button wire:click="sortByMine">
                        <p class="text-blue-500 text-sm font-semibold">
                            Mine
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
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
