@extends('installer::master')

@section('content')
<div class="bg-white shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <h3 class="text-base font-semibold leading-6 text-gray-900">Requirements</h3>
      <div class="mt-2 max-w-xl text-sm text-gray-500">
        <p>Please review if you have all the requirements for WemX</p>
      </div>

        <ul role="list" class="mt-6 space-y-3 text-sm leading-6 text-gray-600 mb-6">
            <li class="flex gap-x-3">
                <svg class="h-6 w-5 flex-none text-green-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"></path>
                </svg>
                PHP 8.1 or above
            </li>
            <li class="flex gap-x-3">
                <svg class="h-6 w-5 flex-none text-green-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"></path>
                </svg>
                MysQL 5.7.22 or higher
            </li>
            <li class="flex gap-x-3">
                <svg class="h-6 w-5 flex-none text-red-600" viewBox="0 0 23 20" fill="currentColor" aria-hidden="true">
                    <path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm4.207 12.793-1.414 1.414L12 13.414l-2.793 2.793-1.414-1.414L10.586 12 7.793 9.207l1.414-1.414L12 10.586l2.793-2.793 1.414 1.414L13.414 12l2.793 2.793z"></path>
                </svg>
                Composer v2
            </li>
        </ul>

        <div>
            <label for="license" class="block text-sm font-medium leading-6 text-gray-900">License Key</label>
            <div class="mt-2">
              <input type="text" name="license" id="license" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="WEMX-XXXXXXXXXXXXXXXXXXX" aria-describedby="email-description">
            </div>
            <p class="mt-2 text-sm text-gray-500" id="license-description">Please enter your license key.</p>
        </div>

      <div class="mt-5 text-right">
        <button type="button" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">Next Step</button>
      </div>
    </div>
  </div>  
@endsection