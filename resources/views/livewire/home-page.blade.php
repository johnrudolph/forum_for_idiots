<div wire:poll>
    <div class="flex flex-col gap-y-4">

        <!-- Recent word of the day -->
        @if($word_of_the_day_yesterday !== null)
        <div class="flex flex-col w-full bg-gray-700 border border-color-white rounded-lg">
            <div class="bg-gray-800 rounded-t-lg">
                <p class="pt-4 pl-4 pb-2 text-white text-lg leading-7 font-semibold tracking-wide">
                    Recent Word of the Day
                </p>
            </div>
            <div>
                <p class="pt-3 px-4 text-white text-md font-bold leading-7">
                    {{ $word_of_the_day_yesterday->title }}
                </p>
                <p class="py-2 px-4 text-white text-sm leading-7">
                    {{ $word_of_the_day_yesterday_definition->text }}
                </p>
            </div>
            <div class="pt-2 bg-gray-800 rounded-b-lg pl-4 pb-2 flex flex-col-4 gap-x-2">
                <div wire:click="upvoteWordOfTheDay">
                    <x-icons.upvote/>
                </div>
                <div>
                    <p class="text-white">
                        {{ $word_of_the_day_yesterday->score }}
                    </p>
                </div>
                <div wire:click="downvoteWordOfTheDay">
                    <x-icons.downvote/>
                </div>
                <div>
                    <p class="pt-1 px-4 pb-2 text-xs italic text-white">
                        Word submitted by {{$word_of_the_day_yesterday->user->name}}. Definition submitted by {{ $word_of_the_day_yesterday_definition->user->name }}.
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Recent advice -->
        @if($advice_yesterday !== null)
        <div class="flex flex-col w-full bg-gray-700 border font-bold border-color-white rounded-lg">
            <div class="bg-gray-800 rounded-t-lg">
                <p class="pt-4 pl-4 pb-2 text-white text-lg leading-7 font-semibold tracking-wide">
                    Recent Advice
                </p>
            </div>
            <div>
                <p class="pt-3 px-4 text-white text-md font-semi-bold leading-7">
                    {{ $advice_yesterday->title }}
                </p>
                <p class="pt-2 px-4 pb-4 text-white font-normal text-sm leading-7">
                    {{ $advice_yesterday_answer->text }}
                </p>
            </div>
            <div class="pt-2 bg-gray-800 rounded-b-lg pl-4 pb-2 flex flex-col-4 gap-x-2">
                <div wire:click="upvoteAdvice">
                    <x-icons.upvote/>
                </div>
                <div>
                    <p class="text-white">
                        {{ $advice_yesterday->score }}
                    </p>
                </div>
                <div wire:click="downvoteAdvice">
                    <x-icons.downvote/>
                </div>
                <div>
                    <p class="pt-1 px-4 pb-2 font-normal text-xs italic text-white">
                        Question submitted by {{$advice_yesterday->user->name}}. Answer submitted by {{ $advice_yesterday_answer->user->name }}.
                    </p>
                </div>
            </div>
        </div>
        @endif

        <!-- Submit something new -->
        <div class="flex flex-col w-full bg-gray-700 border border-color-white rounded-lg">
            <div class="bg-gray-800 rounded-t-lg">
                <p class="pt-4 pl-4 pb-2 text-white text-lg leading-7 font-semibold tracking-wide">
                    Submit something new
                </p>
            </div>
            <div>
                <p class="pt-3 px-4 text-white text-md font-semi-bold leading-7">
                    Need help defining a word?
                </p>
                <button wire:click="submitNewWord">
                    <p class="pt-2 px-4 pb-4 text-left text-blue-500 text-sm">
                        Add a new word and vote on upcoming submissions
                    </p>
                </button>
            </div>
            <div>
                <p class="px-4 text-white text-md font-semi-bold leading-7">
                    Need some advice?
                </p>
                <button wire:click="askForAdvice">
                    <p class="pt-2 px-4 pb-4 text-blue-500 text-sm text-left">
                        Ask for advice and vote on upcoming submissions
                    </p>
                </button>
            </div>
        </div>

        <!-- Today's questions -->
        @if($word_of_the_day !== null && $advice !== null)
        <div class="flex flex-col w-full bg-gray-700 border border-color-white rounded-lg">
            <div class="bg-gray-800 rounded-t-lg">
                <p class="pt-4 pl-4 pb-2 text-white text-lg leading-7 font-semibold tracking-wide">
                    Answer today's questions
                </p>
            </div>
            @if($word_of_the_day !== null)
            <div>
                <p class="pt-3 px-4 text-white text-md font-semi-bold leading-7">
                    Word of the day: {{ $word_of_the_day->title }}
                </p>
                <button wire:click="submitDefinition">
                    <p class="pt-2 px-4 pb-4 text-left text-blue-500 text-sm">
                        Submit a definition
                    </p>
                </button>
            </div>
            @endif
            @if($advice !== null)
            <div>
                <p class="px-4 text-white text-md font-semi-bold leading-7">
                    Today's question: {{ $advice->title }}
                </p>
                <button wire:click="submitAdvice">
                    <p class="pt-2 px-4 pb-4 text-blue-500 text-sm text-left">
                        Submit some advice 
                    </p>
                </button>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
