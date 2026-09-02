(function($){
	'use strict';
	
    var panorama, viewer, container;
    var omero_panorama = $('.single-virtual_tour .omero-panorama-image'),
        data = omero_panorama.data('settings');
    container = document.querySelector('.omero-panorama-image');
    panorama = new PANOLENS.ImagePanorama(data.img);
    viewer = new PANOLENS.Viewer({container: container});
    viewer.add(panorama);

})(jQuery);

