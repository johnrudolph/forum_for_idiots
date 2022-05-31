<div>
    <div class="flex flex-col gap-y-4">
        <div class="mt-8">
            <p class="text-white text-xl font-bold justify-center">
                Forum for Idiots
            </p>
        </div>

        <div>
            <p class="text-white text-md justify-center">
                Stop looking up words in the dictionary and consulting ancient wisdom texts to answer your burning questions. 
                Just ask the internet, and someone even dumber than you will give you the answer.
            </p>
        </div>

        <div class="mt-2 flex flex-cols-2 justify-center">
            <div class="px-2">
                <button 
                    type="button" 
                    onclick=window.location="{{ route('register') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-black hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <a>Create Account</p>
                </button>
            </div>
            <div class="h-full">
                <button 
                    type="button" 
                    onclick=window.location="{{ route('login') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-700 hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <a>Log In</a>
                </button>
            </div>
        </div>

        <!-- Recent word of the day -->
        <div class="flex flex-col w-full bg-gray-700 border border-color-white rounded-lg">
            <div class="bg-gray-800 rounded-t-lg">
                <p class="pt-4 pl-4 pb-2 text-white text-lg leading-7 font-semibold tracking-wide">
                    Recent Word of the Day
                </p>
            </div>
            <div>
                <p class="pt-3 px-4 text-white text-md font-semi-bold leading-7">
                    {{ $word_of_the_day_yesterday->title }}
                </p>
                <p class="pt-2 px-4 text-white text-sm leading-7">
                    {{ $word_of_the_day_yesterday_definition->text }}
                </p>
                <p class="italic pt-2 px-4 pb-2 text-white text-xs">
                    Word submitted by {{ $word_of_the_day_yesterday->user->name }}. Definition submitted by {{ $word_of_the_day_yesterday_definition->user->name }}
                </p>
            </div>
        </div>

        <!-- Recent advice -->
        <div class="flex flex-col w-full bg-gray-700 border border-color-white rounded-lg">
            <div class="bg-gray-800 rounded-t-lg">
                <p class="pt-4 pl-4 pb-2 text-white text-lg leading-7 font-semibold tracking-wide">
                    Recent Advice
                </p>
            </div>
            <div>
                <p class="pt-3 px-4 text-white text-md font-semi-bold leading-7">
                    {{ $advice_yesterday->title }}
                </p>
                <p class="pt-2 px-4 text-white text-sm leading-7">
                    {{ $advice_yesterday_answer->text }}
                </p>
                <p class="italic pt-2 px-4 pb-2 text-white text-xs">
                    Question submitted by {{ $advice_yesterday->user->name }}. Advice submitted by {{ $advice_yesterday_answer->user->name }}
                </p>
            </div>
        </div>
    </div>
</div>
