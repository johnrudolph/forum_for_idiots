<div>
    @if($question !== null)
    <div class="mt-8 flex flex-col gap-y-4">
        @if($at_max_submissions === false)
            <form wire:submit.prevent="submitNewAdvice">
                <div class="flex flex-col w-full bg-gray-700 border border-color-white rounded-lg">
                    <div class="bg-gray-800 rounded-t-lg">
                        <p class="pt-4 pl-4 pb-2 text-white text-lg leading-7 font-semibold tracking-wide">
                            {{ $question->title }}
                        </p>
                    </div>
                    <div>
                    <div class="mt-1 px-4 py-4 sm:mt-0 sm:col-span-2">
                        <input 
                            type="text" 
                            name="new_advice" 
                            id="new_advice" 
                            wire:model="new_advice"
                            class="max-w-full block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md"
                        >
                        <p class="text-white text-sm leading-7 pt-2">
                            Advice must be 3-250 characters
                        </p>
                        <div>
                            @if (session()->has('message'))
                                <div class="pt-1 text-red-600 text-sm">
                                    {{ session('message') }}
                                </div>
                            @endif
                        </div>
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
                        You've done more than enough
                    </p>
                </div>
                <div>
                    <p class="py-3 px-4 text-white text-sm font-semi-bold leading-7">
                        You submitted some lovely advice, so you cannot submit any more for now. If you delete it, you may submit again.
                    </p>
                </div>
            </div>
        @endif

        <div>
            <div class="flow-root mt-6">
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
                            My Defition
                        </p>
                    </button>
                </div>
                <ul role="list" class="-my-4 divide-y divide-gray-200">
                    @foreach($advice_submissions as $advice_submission)
                        <li class="py-5">
                            <div class="relative">
                                <h3 class="text-md font-semibold text-white">
                                    {{ $advice_submission->text }}
                                </h3>
                            </div>
                            <div class="flex flex-col-3 items-center gap-x-2">
                                <div class="pt-2 flex flex-col-3 gap-x-2">
                                    <div wire:click="upvoteAdvice( {{ $advice_submission->id }} )">
                                        <x-icons.upvote/>
                                    </div>
                                    <div>
                                        <p class="text-white">
                                            {{ $advice_submission->score }}
                                        </p>
                                    </div>
                                    <div wire:click="downvoteAdvice( {{ $advice_submission->id }} )">
                                        <x-icons.downvote/>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-white text-sm">
                                        {{ $advice_submission->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                @if($advice_submission->user_id === $user->id || $user->is_moderator)
                                    <div>
                                        <button wire:click="delete( {{$advice_submission->id}} )">
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
    @else
        <div class="mt-8 flex flex-col">
            <div class="flex flex-col w-full bg-gray-700 border border-color-white rounded-lg">
                <p class="pt-4 pl-4 pb-2 text-white text-lg leading-7 font-semibold tracking-wide">
                    No questions were queued for today. <a class="text-blue-500" href='/submit-question'>Submit more questions<a> to get started.
                </p>
            </div>
        </div>
    @endif
</div>
