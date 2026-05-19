<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatApp - Discord Style</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @vite(['resources/js/app.js'])

    <style>
        /* Custom scrollbar ala Discord (Dark Mode) */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #2b2d31; }
        ::-webkit-scrollbar-thumb { background: #1a1b1e; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #111214; }
    </style>
</head>
<body class="bg-[#1e1f22] h-screen overflow-hidden flex justify-center items-center font-sans text-gray-300">

    <div x-data="chatApp()" x-init="initApp()" class="flex h-[95vh] w-[95vw] max-w-6xl bg-[#313338] rounded-xl shadow-2xl overflow-hidden border border-[#1e1f22]">
        
        <div class="w-[300px] bg-[#2b2d31] flex flex-col shrink-0">
            
            <div class="h-12 px-4 flex items-center border-b border-[#1f2023] shadow-sm">
                <div class="w-full bg-[#1e1f22] text-gray-400 text-sm px-3 py-1.5 rounded-md text-center cursor-pointer hover:bg-[#111214] transition">
                    Find or start a conversation
                </div>
            </div>

            <div class="px-3 pt-3 pb-2">
                <div class="flex bg-[#1e1f22] p-1 rounded-md text-xs font-medium">
                    <button @click="tab = 'chats'" :class="tab === 'chats' ? 'bg-[#3f4147] text-gray-100 shadow' : 'text-gray-400 hover:text-gray-200 hover:bg-[#313338]'" class="flex-1 py-1.5 rounded transition">Direct Messages</button>
                    <button @click="tab = 'users'; fetchUsers()" :class="tab === 'users' ? 'bg-[#3f4147] text-gray-100 shadow' : 'text-gray-400 hover:text-gray-200 hover:bg-[#313338]'" class="flex-1 py-1.5 rounded transition">New Chat</button>
                </div>
            </div>

            <div x-show="tab === 'chats'" class="flex-1 overflow-y-auto px-2 space-y-[2px]">
                <template x-for="conv in conversations" :key="conv.id">
                    <div @click="selectConversation(conv)" 
                         :class="activeConversation && activeConversation.id === conv.id ? 'bg-[#404249] text-gray-100' : 'hover:bg-[#35373c] text-gray-400 hover:text-gray-200'"
                         class="flex items-center px-2 py-1.5 cursor-pointer rounded-md transition group">
                        
                        <div class="relative shrink-0">
                            <div class="w-8 h-8 rounded-full bg-[#5865F2] flex items-center justify-center text-white font-bold text-sm">
                                <span x-text="conv.name.charAt(0).toUpperCase()"></span>
                            </div>
                            <template x-if="conv.type === 'private' && isUserOnline(conv)">
                                <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-[#23a559] border-[3px] border-[#2b2d31] rounded-full z-10 group-hover:border-[#35373c]"></div>
                            </template>
                        </div>

                        <div class="ml-3 flex-1 overflow-hidden">
                            <div class="font-medium text-[15px] truncate leading-tight" x-text="conv.name"></div>
                            <div class="text-[11px] truncate mt-0.5 opacity-70" x-text="conv.last_message ? conv.last_message.body : 'Belum ada pesan'"></div>
                        </div>
                    </div>
                </template>
            </div>

            <div x-show="tab === 'users'" class="flex-1 overflow-y-auto px-2 space-y-[2px]">
                <div class="px-2 py-2 text-[11px] font-bold text-gray-400 uppercase tracking-wider">All Users</div>
                <template x-for="u in users" :key="u.id">
                    <div @click="startPrivateChat(u.id)" class="flex items-center px-2 py-1.5 hover:bg-[#35373c] text-gray-400 hover:text-gray-200 rounded-md cursor-pointer transition group">
                        <div class="relative shrink-0">
                            <div class="w-8 h-8 rounded-full bg-gray-600 flex items-center justify-center text-white font-bold text-sm" x-text="u.name.charAt(0)"></div>
                            <template x-if="u.is_online">
                                <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-[#23a559] border-[3px] border-[#2b2d31] rounded-full z-10 group-hover:border-[#35373c]"></div>
                            </template>
                        </div>
                        <div class="ml-3 font-medium text-[15px]" x-text="u.name"></div>
                    </div>
                </template>
            </div>

            <div class="bg-[#232428] h-[52px] flex items-center justify-between px-2 mt-auto shrink-0">
                <div class="flex items-center hover:bg-[#3f4147] py-1 px-1.5 rounded cursor-pointer transition w-full overflow-hidden">
                    <div class="relative shrink-0">
                        <div class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center text-white font-bold">{{ substr(auth()->user()->name, 0, 1) }}</div>
                        <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-[#23a559] border-[3px] border-[#232428] rounded-full"></div>
                    </div>
                    <div class="ml-2 overflow-hidden flex-1">
                        <div class="text-[13px] font-bold text-white leading-tight truncate">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-gray-400 leading-tight">Online</div>
                    </div>
                </div>
                
                <form method="POST" action="/logout" class="inline shrink-0 ml-1">
                    @csrf
                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-400 rounded hover:bg-[#3f4147] transition" title="Logout">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </div>

        <div class="flex-1 flex flex-col bg-[#313338]">
            
            <template x-if="activeConversation">
                <div class="flex flex-col h-full">
                    
                    <div class="h-12 px-4 border-b border-[#1f2023] shadow-sm flex items-center justify-between shrink-0">
                        <div class="flex items-center">
                            <span class="text-gray-400 text-2xl font-light mr-2 select-none" x-text="activeConversation.type === 'group' ? '#' : '@'"></span>
                            <h2 class="font-bold text-gray-100 text-[15px]" x-text="activeConversation.name"></h2>
                            
                            <div class="w-px h-5 bg-gray-600 mx-3"></div>
                            
                            <p class="text-xs font-medium" 
                               :class="isUserOnline(activeConversation) ? 'text-[#23a559]' : 'text-gray-400'" 
                               x-text="getLastSeenText(activeConversation)"></p>
                        </div>
                    </div>

                    <div id="message-container" class="flex-1 overflow-y-auto pt-4 pb-2 px-4 flex flex-col space-y-1.5 bg-black">
                        <template x-for="msg in messages" :key="msg.id">
                            
                            <div class="flex w-full" :class="msg.user_id == {{ auth()->id() }} ? 'justify-end' : 'justify-start'">
                                
                                <div class="relative max-w-[85%] sm:max-w-[75%] px-3 pt-2 pb-1.5 rounded-2xl shadow-sm"
                                     :class="msg.user_id == {{ auth()->id() }} ? 'bg-gradient-to-r from-violet-600 to-fuchsia-600 text-white rounded-tr-sm' : 'bg-[#262626] text-white rounded-tl-sm'">
                                    
                                    <p class="whitespace-pre-wrap break-words leading-relaxed text-[15px] pr-12" x-text="msg.body"></p>
                                    
                                    <span class="absolute bottom-1 right-3 text-[10px]" 
                                          :class="msg.user_id == {{ auth()->id() }} ? 'text-white/80' : 'text-gray-400'" 
                                          x-text="msg.created_at"></span>
                                </div>

                            </div>
                        </template>
                    </div>

                    <div class="px-4 pb-4 pt-2 shrink-0 bg-black">
                        
                        <div class="h-5 flex items-center px-1 mb-1">
                            <p x-show="typingUser" style="display: none;" class="text-[12.5px] font-medium text-violet-500 italic" x-text="typingUser + ' sedang mengetik...'"></p>
                        </div>

                        <form @submit.prevent="sendMessage()" class="bg-[#262626] rounded-full flex items-center px-2 py-1.5">
                            
                            <button type="button" class="bg-violet-600 text-white p-1.5 rounded-full hover:bg-violet-700 transition ml-1 mr-2 flex items-center justify-center shadow-md">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
                            </button>
                            
                            <input type="text" x-model="newMessage" @input="notifyTyping()" 
                                   placeholder="kirim pesan..." 
                                   class="flex-1 bg-transparent border-none px-2 text-white text-[15px] focus:outline-none placeholder-gray-500">
                            
                            <button type="submit" x-show="newMessage.trim().length > 0" class="text-violet-500 font-bold px-4 text-sm hover:text-violet-400 transition">
                                Kirim
                            </button>
                        </form>
                    </div>
            </template>

            <template x-if="!activeConversation">
                <div class="flex-1 flex flex-col items-center justify-center text-gray-400 h-full">
                    <div class="w-24 h-24 mb-4 opacity-50">
                        <svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12c0 5.52 4.48 10 10 10s10-4.48 10-10C22 6.48 17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-12h2v4h-2zm0 6h2v2h-2z"></path></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-300 mb-1">No text channels</h3>
                    <p class="text-sm">Select a user or group from the sidebar to start chatting.</p>
                </div>
            </template>
        </div>

    </div>

    <script>
        function chatApp() {
            return {
                tab: 'chats',
                conversations: [],
                users: [],
                activeConversation: null,
                activeChannelId: null,
                messages: [],
                newMessage: '',
                onlineUsers: [], 
                
                typingUser: false,
                typingTimeout: null,

                initApp() {
                    this.fetchConversations();
                    this.fetchUsers();
                    this.connectToPresenceChannel();
                    this.updateMyPresence('online');
                    setInterval(() => this.updateMyPresence('online'), 30000);
                },

                fetchConversations() {
                    fetch('/conversations')
                        .then(res => res.json())
                        .then(data => { this.conversations = data; });
                },

                fetchUsers() {
                    fetch('/users')
                        .then(res => res.json())
                        .then(data => { this.users = data; });
                },

                selectConversation(conv) {
                    this.activeConversation = conv;
                    this.fetchMessages(conv.id);
                    this.listenToConversationChannel(conv.id);
                },

                fetchMessages(id) {
                    fetch(`/conversations/${id}/messages`)
                        .then(res => res.json())
                        .then(data => {
                            this.messages = data;
                            this.scrollToBottom();
                        });
                },

                notifyTyping() {
                    if (!this.activeConversation) return;
                    
                    window.Echo.private(`chat.${this.activeConversation.id}`)
                        .whisper('typing', {
                            user_name: '{{ auth()->user()->name }}'
                        });
                },

                sendMessage() {
                    if (!this.newMessage.trim()) return;

                    let bodyData = { body: this.newMessage };
                    let currentConvId = this.activeConversation.id;

                    fetch(`/conversations/${currentConvId}/messages`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(bodyData)
                    })
                    .then(res => res.json())
                    .then(msg => {
                        this.messages.push(msg);
                        this.newMessage = '';
                        this.scrollToBottom();
                        this.fetchConversations();
                    });
                },

                startPrivateChat(userId) {
                    fetch('/conversations/private', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ user_id: userId })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.tab = 'chats';
                        this.fetchConversations();
                        setTimeout(() => {
                            let found = this.conversations.find(c => c.id === data.conversation_id);
                            if (found) this.selectConversation(found);
                        }, 500);
                    });
                },

                updateMyPresence(status) {
                    fetch('/presence', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ status: status })
                    });
                },

                connectToPresenceChannel() {
                    setTimeout(() => {
                        if (!window.Echo) return;
                        
                        window.Echo.join('presence.chat')
                            .here((users) => {
                                this.onlineUsers = users.map(u => u.id);
                            })
                            .joining((user) => {
                                if (!this.onlineUsers.includes(user.id)) this.onlineUsers.push(user.id);
                            })
                            .leaving((user) => {
                                this.onlineUsers = this.onlineUsers.filter(id => id !== user.id);
                            })
                            .listen('.user.presence.changed', (e) => {
                                this.fetchConversations();
                                if (this.tab === 'users') this.fetchUsers();
                            });
                    }, 1000);
                },

                getLastSeenText(conv) {
                    if (!conv) return '';
                    if (conv.type === 'group') return 'Grup Chat';
                    
                    if (!conv.users) return 'Offline';
                    let kawanId = conv.users.find(u => u.id != {{ auth()->id() }})?.id;
                    
                    if (this.onlineUsers.includes(kawanId)) {
                        return 'Online';
                    }

                    let kawan = this.users.find(u => u.id === kawanId);
                    
                    if (kawan && kawan.last_seen_at) {
                        let date = new Date(kawan.last_seen_at);
                        let now = new Date();
                        let hours = date.getHours().toString().padStart(2, '0');
                        let minutes = date.getMinutes().toString().padStart(2, '0');
                        
                        if(date.toDateString() === now.toDateString()) {
                            return 'Last seen today at ' + hours + ':' + minutes;
                        } else {
                            return 'Last seen ' + date.getDate() + '/' + (date.getMonth()+1) + ' at ' + hours + ':' + minutes;
                        }
                    }

                    return 'Offline';
                },

                isUserOnline(conv) {
                    if (!conv || !conv.users) return false;
                    let kawan = conv.users.find(u => u.id != {{ auth()->id() }});
                    return kawan ? this.onlineUsers.includes(kawan.id) : false;
                },

                scrollToBottom() {
                    setTimeout(() => {
                        let container = document.getElementById('message-container');
                        if (container) container.scrollTop = container.scrollHeight;
                    }, 50);
                },

                listenToConversationChannel(conversationId) {
                    if (this.activeChannelId) {
                        window.Echo.leave(`chat.${this.activeChannelId}`);
                    }
                    
                    this.activeChannelId = conversationId;

                    window.Echo.private(`chat.${conversationId}`)
                        .listen('.message.sent', (e) => {
                            if (this.activeConversation && this.activeConversation.id === e.conversation_id) {
                                if(e.user_id != {{ auth()->id() }}) {
                                    
                                    this.typingUser = false;

                                    let pesanSudahAda = this.messages.some(m => m.id === e.id);
                                    if (!pesanSudahAda) {
                                        this.messages.push({
                                            id: e.id,
                                            user_id: e.user_id,
                                            user_name: e.user_name,
                                            body: e.body,
                                            created_at: e.created_at
                                        });
                                        this.scrollToBottom();
                                    }
                                }
                            }
                            this.fetchConversations(); 
                        })
                        .listenForWhisper('typing', (e) => {
                            this.typingUser = e.user_name;
                            
                            if (this.typingTimeout) {
                                clearTimeout(this.typingTimeout);
                            }
                            
                            this.typingTimeout = setTimeout(() => {
                                this.typingUser = false;
                            }, 2000);
                        });
                }
            }
        }
    </script>
</body>
</html>