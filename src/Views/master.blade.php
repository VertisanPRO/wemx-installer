<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp"></script>

    <title>Document</title>
</head>

<body class="bg-gray-100 flex justify-center items-center h-screen">
    <div class="app container mx-auto px-4">

      <div class="sm:flex mb-6">
        <div class="mb-4 flex-shrink-0 sm:mb-0 sm:mr-4">
          <img class="h-16 w-16 border border-gray-300 bg-white text-gray-300 rounded" src="https://imgur.com/oJDxg2r.png" alt="WemX Logo">
        </div>
        <div>
          <h4 class="text-lg font-bold">WemX</h4>
          <p class="mt-1">Take your business to new heights with our innovative software solutions.</p>
        </div>
      </div>
      
      <div class="overflow-hidden rounded-lg bg-white shadow mb-6">
        <div class="px-4 py-5 sm:p-6">
          <div class="progress">
              <div class="lg:border-b lg:border-t lg:border-gray-200">
                  <nav class="mx-auto max-w-7xl px-4 sm:px-4 lg:px-6" aria-label="Progress">
                    <ol role="list" class="overflow-hidden rounded-md lg:flex lg:rounded-none lg:border-l lg:border-r lg:border-gray-200">
                      <li class="relative overflow-hidden lg:flex-1">
                        <div class="overflow-hidden border border-gray-200 lg:border-0">
                          <!-- Current Step -->
                          <a href="#" aria-current="step">
                            <span class="absolute left-0 top-0 h-full w-1 bg-indigo-600 lg:bottom-0 lg:top-auto lg:h-1 lg:w-full" aria-hidden="true"></span>
                            <span class="flex items-start px-6 py-5 text-sm font-medium lg:pl-9">
                              <span class="flex-shrink-0">
                                @if($step > 1)
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600">
                                  <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 01.208 1.04l-9 13.5a.75.75 0 01-1.154.114l-6-6a.75.75 0 011.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 011.04-.208z" clip-rule="evenodd"></path>
                                  </svg>
                                </span>
                                @else 
                                <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-indigo-600">
                                  <span class="text-indigo-600">01</span>
                                </span>
                                @endif
                              </span>
                              <span class="ml-4 mt-0.5 flex min-w-0 flex-col">
                                <span class="text-sm font-medium @if($step == 1) text-indigo-600 @endif">Requirements</span>
                                <span class="text-sm font-medium text-gray-500">Minimum requirements</span>
                              </span>
                            </span>
                          </a>
                        </div>
                      </li>
                      <li class="relative overflow-hidden lg:flex-1">
                        <div class="overflow-hidden border border-gray-200 rounded-b-md border-t-0 lg:border-0">
                          <!-- Upcoming Step -->
                          <a href="#" class="group">
                            @if($step >= 2)
                              <span class="absolute left-0 top-0 h-full w-1 bg-indigo-600 lg:bottom-0 lg:top-auto lg:h-1 lg:w-full" aria-hidden="true"></span>
                            @else 
                              <span class="absolute left-0 top-0 h-full w-1 bg-transparent group-hover:bg-gray-200 lg:bottom-0 lg:top-auto lg:h-1 lg:w-full" aria-hidden="true"></span>
                            @endif
                            <span class="flex items-start px-6 py-5 text-sm font-medium lg:pl-9">
                              <span class="flex-shrink-0">
                                @if($step > 2)
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600">
                                  <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 01.208 1.04l-9 13.5a.75.75 0 01-1.154.114l-6-6a.75.75 0 011.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 011.04-.208z" clip-rule="evenodd"></path>
                                  </svg>
                                </span>
                                @elseif($step == 2)
                                <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-indigo-600">
                                  <span class="text-indigo-600">02</span>
                                </span>
                                @else 
                                <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-gray-300">
                                  <span class="text-gray-500">02</span>
                                </span>
                                @endif
                              </span>
                              <span class="ml-4 mt-0.5 flex min-w-0 flex-col">
                                <span class="text-sm font-medium @if($step == 2) text-indigo-600 @endif">Installation</span>
                                <span class="text-sm font-medium text-gray-500">Install latest WemX version.</span>
                              </span>
                            </span>
                          </a>
                          <!-- Separator -->
                          <div class="absolute inset-0 left-0 top-0 hidden w-3 lg:block" aria-hidden="true">
                            <svg class="h-full w-full text-gray-300" viewBox="0 0 12 82" fill="none" preserveAspectRatio="none">
                              <path d="M0.5 0V31L10.5 41L0.5 51V82" stroke="currentcolor" vector-effect="non-scaling-stroke" />
                            </svg>
                          </div>
                        </div>
                      </li>
                      <li class="relative overflow-hidden lg:flex-1">
                        <div class="overflow-hidden border border-gray-200 rounded-b-md border-t-0 lg:border-0">
                          <!-- Upcoming Step -->
                          <a href="#" class="group">
                              @if($step >= 3)
                                <span class="absolute left-0 top-0 h-full w-1 bg-indigo-600 lg:bottom-0 lg:top-auto lg:h-1 lg:w-full" aria-hidden="true"></span>
                              @else 
                                <span class="absolute left-0 top-0 h-full w-1 bg-transparent group-hover:bg-gray-200 lg:bottom-0 lg:top-auto lg:h-1 lg:w-full" aria-hidden="true"></span>
                              @endif                            <span class="flex items-start px-6 py-5 text-sm font-medium lg:pl-9">
                              <span class="flex-shrink-0">
                                @if($step > 3)
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600">
                                  <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 01.208 1.04l-9 13.5a.75.75 0 01-1.154.114l-6-6a.75.75 0 011.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 011.04-.208z" clip-rule="evenodd"></path>
                                  </svg>
                                </span>
                                @elseif($step == 3)
                                <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-indigo-600">
                                  <span class="text-indigo-600">03</span>
                                </span>
                                @else 
                                <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-gray-300">
                                  <span class="text-gray-500">03</span>
                                </span>
                                @endif
                              </span>
                              <span class="ml-4 mt-0.5 flex min-w-0 flex-col">
                                <span class="text-sm font-medium @if($step == 3) text-indigo-600 @endif">Configuration</span>
                                <span class="text-sm font-medium text-gray-500">Setup app & database.</span>
                              </span>
                            </span>
                          </a>
                          <!-- Separator -->
                          <div class="absolute inset-0 left-0 top-0 hidden w-3 lg:block" aria-hidden="true">
                            <svg class="h-full w-full text-gray-300" viewBox="0 0 12 82" fill="none" preserveAspectRatio="none">
                              <path d="M0.5 0V31L10.5 41L0.5 51V82" stroke="currentcolor" vector-effect="non-scaling-stroke" />
                            </svg>
                          </div>
                        </div>
                      </li>
                      <li class="relative overflow-hidden lg:flex-1">
                        <div class="overflow-hidden border border-gray-200 rounded-b-md border-t-0 lg:border-0">
                          <!-- Upcoming Step -->
                          <a href="#" class="group">
                              @if($step >= 4)
                                <span class="absolute left-0 top-0 h-full w-1 bg-indigo-600 lg:bottom-0 lg:top-auto lg:h-1 lg:w-full" aria-hidden="true"></span>
                              @else 
                                <span class="absolute left-0 top-0 h-full w-1 bg-transparent group-hover:bg-gray-200 lg:bottom-0 lg:top-auto lg:h-1 lg:w-full" aria-hidden="true"></span>
                              @endif                            <span class="flex items-start px-6 py-5 text-sm font-medium lg:pl-9">
                              <span class="flex-shrink-0">
                                @if($step > 4)
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-600">
                                  <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M19.916 4.626a.75.75 0 01.208 1.04l-9 13.5a.75.75 0 01-1.154.114l-6-6a.75.75 0 011.06-1.06l5.353 5.353 8.493-12.739a.75.75 0 011.04-.208z" clip-rule="evenodd"></path>
                                  </svg>
                                </span>
                                @elseif($step == 4)
                                <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-indigo-600">
                                  <span class="text-indigo-600">04</span>
                                </span>
                                @else 
                                <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-gray-300">
                                  <span class="text-gray-500">04</span>
                                </span>
                                @endif
                              </span>
                              <span class="ml-4 mt-0.5 flex min-w-0 flex-col">
                                <span class="text-sm font-medium @if($step == 4) text-indigo-600 @endif">Create Account</span>
                                <span class="text-sm font-medium text-gray-500">Create your admin account.</span>
                              </span>
                            </span>
                          </a>
                          <!-- Separator -->
                          <div class="absolute inset-0 left-0 top-0 hidden w-3 lg:block" aria-hidden="true">
                            <svg class="h-full w-full text-gray-300" viewBox="0 0 12 82" fill="none" preserveAspectRatio="none">
                              <path d="M0.5 0V31L10.5 41L0.5 51V82" stroke="currentcolor" vector-effect="non-scaling-stroke" />
                            </svg>
                          </div>
                        </div>
                      </li>
                    </ol>
                  </nav>
                </div>    
          </div>
        </div>
      </div>
        @yield('content')
    </div>
</body>
</html>