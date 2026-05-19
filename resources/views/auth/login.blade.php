<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ChatApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#1e1f22] h-screen flex justify-center items-center font-sans text-gray-300">
    
    <div class="bg-[#313338] p-8 rounded-lg shadow-2xl w-full max-w-[480px]">
        
        <div class="text-center mb-6">
            <h2 class="text-[24px] font-bold text-[#f2f3f5] mb-2">Welcome back!</h2>
            <p class="text-[#b5bac1] text-[15px]">We're so excited to see you again!</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 p-3 rounded mb-6 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-[#b5bac1] text-[12px] font-bold uppercase mb-2 tracking-wide">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="off"
                       class="w-full bg-[#1e1f22] text-[#dbdee1] rounded p-2.5 focus:outline-none focus:ring-0 border-none">
            </div>

            <div class="mb-6">
                <label class="block text-[#b5bac1] text-[12px] font-bold uppercase mb-2 tracking-wide">
                    Password <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password" required 
                       class="w-full bg-[#1e1f22] text-[#dbdee1] rounded p-2.5 focus:outline-none focus:ring-0 border-none">
            </div>

            <button type="submit" class="w-full bg-[#5865F2] hover:bg-[#4752C4] text-white font-semibold text-[15px] py-2.5 rounded transition mb-2">
                Log In
            </button>

            <div class="mt-2 text-[14px] text-[#b5bac1]">
                Need an account? <a href="{{ route('register') }}" class="text-[#00a8fc] hover:underline font-medium">Register</a>
            </div>
        </form>
    </div>

</body>
</html>