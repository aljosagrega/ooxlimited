
elementorCommon.elements.$window.on("elementor/nested-element-type-loaded", (function() {
    
    class OmeroNestedCarouselElement extends elementor.modules.elements.types.NestedElementBase {
        getType() {
            return "omero-nested-carousel";
        }
    }
    
    elementor.elementsManager.registerElementType(new OmeroNestedCarouselElement());
}));
