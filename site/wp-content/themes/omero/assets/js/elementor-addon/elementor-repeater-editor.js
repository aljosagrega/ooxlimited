(function ($) {
    $(window).on('elementor:init', function () {
        if (typeof $e.run === 'function') {
            const originalRun = $e.run;
            let isInsertingInner = false;
            let parentCid;
            let parentName = '';
    
            $e.run = function (command, args) {
                if (command === 'document/repeater/insert') {
    
                    const controlName = args.name,
                        controls = args.container.args.settings.options.controls;
                        
                    // console.log(isInsertingInner);
                    // console.log(controlName);
                    // console.log(args);
    
                    if (typeof controls[controlName].parent_field !== 'undefined') {
                        parentCid = null;
                        isInsertingInner = true;
                        parentName = controls[controlName].parent_field;
                    }
                    else if (controlName === parentName) {
                        if (isInsertingInner) {
    
                            // Reset?
                            isInsertingInner = false;
                            parentName = '';
    
                            let parentFound = null;
                            // let container = new Backbone.ChildViewContainer();
                            
                            if (args.container.repeaters[controlName].children) {
                                let children = args.container.repeaters[controlName].children;  
                                // console.log(parentCid);
                                // console.log(children);
    
                                for (let i = 0; i < children.length; i++) {
                                    if (children[i].settings.cid === parentCid) {
                                        // container.add(parentFound);
                                        parentFound = children[i].settings;
                                        break;
                                    }
                                }
    
                                if (parentFound) {
                                    let _id = parentFound.attributes._id;
                                    $('[data-setting="_id"][value="' + _id + '"]')
                                        .closest('.elementor-repeater-fields')
                                        .find('.elementor-repeater-row-item-title')
                                        .click();
    
                                    // parentFound.cid = 'khanh';
                                    // console.log(parentFound);
                                    return parentFound;
                                }
                            }
                            return;
                        }
                    }
                }
    
                const result = originalRun.apply(this, arguments);
                if (isInsertingInner && typeof args.container.settings.cid !== 'undefined') {
                    parentCid = args.container.settings.cid;
                }
    
                return result;
            };
        }

        if (typeof elementorModules.editor.elements.models.BaseSettings.prototype.parseDynamicSettings === 'function') {
            var originalParseDynamicSettings = elementorModules.editor.elements.models.BaseSettings.prototype.parseDynamicSettings;
    
            elementorModules.editor.elements.models.BaseSettings.prototype.parseDynamicSettings = function (settings, options, controls) {
                var self = this;
                settings = elementorCommon.helpers.cloneObject(settings || self.attributes);
                options = options || {};
                controls = controls || this.controls;
                // console.log(settings);
                // console.log(options);
                // console.log(controls);
    
                if (self.collection && self.collection.indexOf) {
                    var itemIndex = self.collection.indexOf(self) + 1;
                    settings._itemIndex = itemIndex;
                    settings.itemIndex = itemIndex; 
                }

                // console.log(settings);
    
                return originalParseDynamicSettings.call(this, settings, options, controls);
            };
        }
    });
})(jQuery);