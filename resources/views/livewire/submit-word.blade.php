<div wire:poll>
    <div class="mt-8 flex flex-col gap-y-4">
        @if($at_max_submissions === false)
            <form wire:submit.prevent="submitNewWord">
                <div class="flex flex-col w-full bg-gray-700 border border-color-white rounded-lg">
                    <div class="bg-gray-800 rounded-t-lg">
                        <p class="pt-4 pl-4 pb-2 text-white text-lg leading-7 font-semibold tracking-wide">
                            Add a new word
                        </p>
                    </div>
                    <div>
                    <div class="mt-1 px-4 py-4 sm:mt-0 sm:col-span-2">
                        <input 
                            type="text" 
                            name="new_word" 
                            id="new_word" 
                            wire:model="new_word"
                            class="max-w-lg block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:max-w-xs sm:text-sm border-gray-300 rounded-md"
                        >
                        <p class="text-white text-sm leading-7 pt-2">
                            Submissions must be 3-50 characters
                        </p>
                    </div>
                    </div>
                    <div class="bg-gray-800 rounded-b-lg px-4 py-2 flex">
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent text-sm leading-5 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out">
                            Submit
                        </button>
                    </div>
                </div>
            </form>
        @else
            <div class="flex flex-col w-full bg-gray-700 border border-color-white rounded-lg">
                <div class="bg-gray-800 rounded-t-lg">
                    <p class="pt-4 pl-4 pb-2 text-white text-lg leading-7 font-semibold tracking-wide">
                        That's a lot of good ideas!
                    </p>
                </div>
                <div>
                    <p class="py-3 px-4 text-white text-sm font-semi-bold leading-7">
                        You have 3 words in the queue, so you cannot submit any more for now. If you delete one of them, you may submit more.
                    </p>
                </div>
            </div>
        @endif

        <div>
            <div class="flow-root mt-6">
                <div>
                    <p class="text-white text-lg font-semibold">
                        Vote for upcoming words
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
                            My Words
                        </p>
                    </button>
                </div>
                <ul role="list" class="-my-4 divide-y divide-gray-200">
                    @foreach($words as $word)
                        <li class="py-5">
                            <div class="relative">
                                <h3 class="text-md font-semibold text-white">
                                    {{ $word->title }}
                                </h3>
                            </div>
                            <div class="flex flex-col-3 items-center gap-x-2">
                                <div class="pt-2 flex flex-col-3 gap-x-2">
                                    <div wire:click="upvoteWord( {{ $word->id }} )">
                                        <x-icons.upvote/>
                                    </div>
                                    <div>
                                        <p class="text-white">
                                            {{ $word->score }}
                                        </p>
                                    </div>
                                    <div wire:click="downvoteWord( {{ $word->id }} )">
                                        <x-icons.downvote/>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-white text-sm">
                                        {{ $word->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                @if($word->created_by === $user->id)
                                    <div>
                                        <button wire:click="delete( {{$word->id}} )">
                                            <p class="text-red-500 text-sm">
                                                Delete
                                            </p>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
