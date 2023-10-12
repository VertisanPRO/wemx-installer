@extends('installer::master', [
  'step' => 2
])

@section('content')
<div class="bg-white shadow sm:rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <h3 class="text-base font-semibold text-lg leading-6 text-gray-900">Installation</h3>
      <div class="mt-2 max-w-xl text-sm text-gray-500 mb-6">
        <p>Please select the version you want to install</p>
      </div>

      <div class="mb-6" id="license_div">
        <label for="license" class="block text-sm font-medium leading-6 text-gray-900">License Key</label>
        <div class="mt-2">
          <input type="text" name="license" id="license" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="WEMX-XXXXXXXXXXXXXXXXXXXXXX" aria-describedby="email-description">
        </div>
        <p class="mt-2 text-sm text-gray-500" id="license-description">Please enter your license key.</p>
      </div>

      <div class="flex items-center hidden" id="animation_div">
        <div class="mr-6">
          <img src="https://raw.githubusercontent.com/n3r4zzurr0/svg-spinners/main/preview/bars-scale-black-36.svg" style="visibility:visible;max-width:100%;">
        </div>
        <div class="w-full">
          <label for="license" class="block text-sm font-medium leading-6 text-gray-900 mb-2">Downloading...</label>
          <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div class="bg-indigo-600 h-2.5 rounded-full" id="progress_bar" style="width: 0%"></div>
          </div>
          <p class="mt-2 text-sm text-gray-500" id="license-description">Preparing for installation</p>
        </div>
      </div>

      <div class="mt-5 text-right">
        <button type="button" id="btn-install" onclick="install()" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
          Install
        </button>
      </div>
    </div>
</div> 

<script>
  function install() {
    const install_button = document.getElementById('btn-install');
    const license_div = document.getElementById('license_div');
    const animation_div = document.getElementById('animation_div');

    install_button.innerHTML = 
    `
    <svg aria-hidden="true" role="status" class="inline w-4 h-4 mr-3 text-white animate-spin" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="#E5E7EB"/>
            <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="currentColor"/>
    </svg>
    Installing...
    `;

    license_div.classList.add("hidden");
    animation_div.classList.remove("hidden");

    increaseProgress();
  }

  function increaseProgress() {
    const progressBar = document.getElementById('progress_bar');
    const install_button = document.getElementById('btn-install');

    let currentWidth = parseFloat(getComputedStyle(progressBar).width);
    let containerWidth = parseFloat(getComputedStyle(progressBar.parentElement).width);
    let currentPercentage = (currentWidth / containerWidth) * 100;

    if (currentPercentage < 100) {
        // Increase the current percentage by a small amount (e.g., 1%)
        // You can adjust this value to change the speed of progress
        currentPercentage += 1;
        progressBar.style.width = `${currentPercentage}%`;

        if(currentPercentage >= 100) {
          install_button.href =  '/install/configuration';
          install_button.innerHTML =  `Next Step`;
        }

        // Call the function again after a short delay (e.g., 100 milliseconds)
        // You can adjust this value to change the update frequency
        setTimeout(increaseProgress, Math.floor(Math.random() * 50));
    }
  }
</script>
@endsection