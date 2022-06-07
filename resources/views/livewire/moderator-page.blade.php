<div>
    <div class="mt-8 flex flex-col gap-y-4">
        <form wire:submit.prevent="submit">
            <div class="flex flex-col w-full bg-gray-700 border border-color-white rounded-lg">
                <div class="bg-gray-800 rounded-t-lg">
                    <p class="pt-4 pl-4 pb-2 text-white text-lg leading-7 font-semibold tracking-wide">
                        Add a new word
                    </p>
                </div>
                <div>
                    <div class="mt-1 px-4 py-4 sm:mt-0 sm:col-span-2">
                        <p class="text-white text-sm font-bold leading-7">
                            Super Secret Password
                        </p>
                        <input 
                            type="text" 
                            name="password" 
                            id="password" 
                            wire:model="password"
                            class="max-w-full w-full block shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md"
                        >
                        <p class="text-white pt-4 text-sm font-bold leading-7">
                            User email
                        </p>
                        <input 
                            type="text" 
                            name="email" 
                            id="email" 
                            wire:model="email"
                            class="max-w-full w-full block shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md"
                        >
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
    </div>
</div>