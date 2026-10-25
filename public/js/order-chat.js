if (typeof livewire !== 'undefined') {

    const chatListDiv = document.getElementById("chatListDiv");
    const chatList = document.getElementById("chatList");
    const emptyChat = document.getElementById("emptyChat");

    if (chatListDiv) chatListDiv.style.display = "none";

    livewire.on("clearChats", data => {
        if (chatList) chatList.innerHTML = "";
    });


    livewire.on("loadChats", data => {
        if (!chatList || !chatListDiv || !emptyChat) return;

        if (data != null && data[1] && data[1].length != 0) {
            emptyChat.style.display = "none";
            chatListDiv.style.display = "block";
            
            const ownerId = data[0];
            const chats = data[1];
            
            chatList.innerHTML = ""; // Clear existing chats

            chats.forEach(chat => {
                let chatSection = "<div class='chat-message'>";
                if (chat['userId'] == ownerId) {
                    chatSection += "<div class='flex flex-col items-start order-2 max-w-xs mx-2 space-y-1 text-xs'>";
                } else {
                    chatSection += "<div class='flex flex-col items-end order-2 max-w-xs mx-2 space-y-1 text-xs'>";
                }
                chatSection += "<div>";
                
                var photos = chat['photos'];
                if (photos != null && photos.length > 0) {
                    chatSection += "<div class='grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-1'>";
                    photos.forEach(photoObject => {
                        var photoSrc = photoObject['url'];
                        if (chat['userId'] == ownerId) {
                            chatSection += '<img src="' + photoSrc + '" class="w-24 h-24 rounded-lg rounded-bl-none">';
                        } else {
                            chatSection += '<img src="' + photoSrc + '" class="w-24 h-24 rounded-lg rounded-br-none">';
                        }
                    });
                    chatSection += "</div>";
                }

                if (chat['text'] != null && chat['text'].length > 0) {
                    if (chat['userId'] == ownerId) {
                        chatSection += '<span class="inline-block px-4 py-2 text-gray-600 bg-gray-300 rounded-lg rounded-bl-none">';
                    } else {
                        chatSection += '<span class="inline-block px-4 py-2 text-gray-100 bg-gray-500 rounded-lg rounded-bl-none">';
                    }
                    chatSection += chat['text'];
                    chatSection += "</span>";
                }
                chatSection += "</div>";
                chatSection += "</div>";
                chatSection += "</div >";
                
                chatList.innerHTML += chatSection;
            });

        } else {
            emptyChat.style.display = "block";
            chatListDiv.style.display = "none";
        }
    });
}

