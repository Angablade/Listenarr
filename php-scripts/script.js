
function handleArtistRequest() {
    var artistName = document.getElementById('artistName').value;

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'artist_request.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                var lidarrResponse = JSON.parse(xhr.responseText);
                console.log('Lidarr Response:', lidarrResponse);

                updateUI(lidarrResponse);
            } else {
                console.error('Error:', xhr.status);
            }
        }
    };
   xhr.send('artistName=' + encodeURIComponent(artistName));
}


function updateUI(lidarrResponse) {
    var resultsContainer = document.getElementById('resultsContainer');

    resultsContainer.innerHTML = '';

    if (lidarrResponse && lidarrResponse.length > 0) {
        lidarrResponse.forEach(function(result) {
            var listItem = document.createElement('li');

            if (result.images && result.images.length > 0) {
                var lastImage = result.images[result.images.length - 1];
                var image = document.createElement('img');
                image.src = lastImage.remoteUrl;
                image.alt = 'Artist Image';
                image.style.width = '50px';
                image.style.height = '50px';
                image.style.borderRadius = '5px';
                image.style.marginRight = '10px';
                listItem.appendChild(image);
            }

            var titleElement = document.createElement('div');
            var titleHeading = document.createElement('h4');
            titleHeading.textContent = result.artistName;
            titleElement.appendChild(titleHeading);

            if (result.overview) {
                var shortOverview = result.overview.substring(0, 200);
                var overviewParagraph = document.createElement('p');
                overviewParagraph.textContent = shortOverview + '...';
                titleElement.appendChild(overviewParagraph);
            }

            var addToLibraryButton = document.createElement('button');
            addToLibraryButton.textContent = 'Add to Library';
            addToLibraryButton.onclick = function() {
				addToLibrary(result);
            };
			
            titleElement.appendChild(addToLibraryButton);
            listItem.appendChild(titleElement);

            resultsContainer.appendChild(listItem);
        });
    } else {
        var noResultsParagraph = document.createElement('p');
        noResultsParagraph.textContent = 'No results found.';
        resultsContainer.appendChild(noResultsParagraph);
    }
}



async function addToLibrary(artistMetadata) {
    var confirmationResult = await showCustomConfirmation('Do you want to add this artist to the library?');

    if (confirmationResult) {
        // Add the usernameFromSession to the artistMetadata object
        artistMetadata.usernameFromSession = usernameFromSession;

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'add_to_library.php', true);
        xhr.setRequestHeader('Content-Type', 'application/json');

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                console.log('Add to Library Response:', xhr.responseText);
                var response = JSON.parse(xhr.responseText);
                showCustomModal(response.success, response.message);
            }
        };

        var artistMetadataJson = JSON.stringify(artistMetadata);
        xhr.send(artistMetadataJson);
    } else {
        console.log('User canceled the operation.');
    }
}

function handleEnterKey(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            handleArtistRequest();
        }
    }
	
	function showCustomModal(success, message) {
		var modal = document.getElementById('myModal');
		var modalTitle = document.getElementById('modalTitle');
		var modalMessage = document.getElementById('modalMessage');
		var closeBtn = document.getElementById('closeBtn');

		modalTitle.textContent = success ? 'Success' : 'Error';
		modalMessage.textContent = message;

		modal.style.display = 'block';

		closeBtn.onclick = function() {
			modal.style.display = 'none';
		};

		window.onclick = function(event) {
			if (event.target == modal) {
				modal.style.display = 'none';
			}
		};
    }
	function showCustomConfirmation(message) {
            return new Promise((resolve) => {
                var confirmationModal = document.getElementById('confirmationModal');
                var confirmationMessage = document.getElementById('confirmationMessage');
                var confirmButton = document.getElementById('confirmButton');
                var cancelButton = document.getElementById('cancelButton');

                confirmationMessage.textContent = message;

                confirmationModal.style.display = 'block';

                confirmButton.onclick = function() {
                    confirmationModal.style.display = 'none';
                    resolve(true);
                };

                cancelButton.onclick = function() {
                    confirmationModal.style.display = 'none';
                    resolve(false);
                };

                window.onclick = function(event) {
                    if (event.target == confirmationModal) {
                        resolve(false);
                        confirmationModal.style.display = 'none';
                    }
                };
            });
        }