jQuery(document).ready(function($) {
    var translations = {
        'en': {
            'share_successful': 'Shared successfully!',
        },
        'uk': {
            'share_successful': 'Відправленно успішно!',
        }
        // Додайте переклади для інших мов за необхідності
    };

    $(document).on('click', '.activity-share-button', function(e) {
        e.preventDefault();
        var activityID = $(this).data('activity-id');
        if (activityID === undefined || activityID === null) {
            console.error('Invalid activity ID');
            return;
        }
        $('#shareModal').data('activity-id', activityID).fadeIn();

        $.ajax({
            url: bpShareActivity.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_friends_list',
            },
            success: function(response) {
                if (response !== null && response !== undefined) {
                    $('#friendsList').html(response);
                    if ($('#friendsList .hidden').length > 0) {
                        $('#showAllFriends').show();
                    } else {
                        $('#showAllFriends').hide();
                    }
                } else {
                    console.error('Invalid response from server');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching friends list:', error);
            }
        });
    });

    $('#shareModalClose').on('click', function() {
        $('#shareModal').fadeOut();
    });

    $('#sendShare').on('click', function() {
        var activityID = $('#shareModal').data('activity-id');
        var selectedFriends = $('#friendsList input:checked').map(function() {
            return $(this).val();
        }).get();

        if (activityID === undefined || activityID === null) {
            console.error('Invalid activity ID');
            return;
        }

        $.ajax({
            url: bpShareActivity.ajaxurl,
            type: 'POST',
            data: {
                action: 'share_activity_with_friends',
                activity_id: activityID,
                friends: selectedFriends,
            },
            success: function(response) {
                $('#shareModal').fadeOut();
                var lang = $('html').attr('lang');
                if (translations[lang] !== undefined && translations[lang]['share_successful'] !== undefined) {
                    alert(translations[lang]['share_successful']);
                } else {
                    alert('Shared successfully!');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error sharing activity:', error);
            }
        });
    });

    $(document).on('click', '#showAllFriendsButton', function() {
        $('#friendsList .hidden').removeClass('hidden');
        $('#showAllFriendsContainer').remove();
    });
});
