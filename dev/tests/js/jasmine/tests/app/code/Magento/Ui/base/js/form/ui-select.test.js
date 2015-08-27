/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
/*jscs:disable requirePaddingNewLinesInObjects*/
/*jscs:disable jsDoc*/

define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/ui-select'
], function (_, registry, Constr) {
    'use strict';

    describe('Magento_Ui/js/form/element/ui-select', function () {

        var obj = new Constr({
            dataScope: '',
            provider: 'provider'
        });

        describe('"initialize" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initialize')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.initialize;

                expect(type).toEqual('function');
            });
        });

        describe('"initObservable" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initObservable')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.initialize;

                expect(type).toEqual('function');
            });
        });

        describe('"outerClick" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('outerClick')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.outerClick;

                expect(type).toEqual('function');
            });
            it('Variable "this.listVisible" must be false ', function () {
                obj.listVisible(true);
                obj.outerClick();
                expect(obj.listVisible()).toEqual(false);
            });
        });

        describe('"hasSelected" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('hasSelected')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.hasSelected;

                expect(type).toEqual('function');
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.hasSelected();

                expect(type).toEqual('boolean');
            });
            it('Must be false if selected array length is 0', function () {
                obj.selected([]);
                obj.hasSelected();
                expect(obj.selected()).toEqual(false);
            });
            it('Must be true if selected array length is 0', function () {
                obj.selected(['magento']);
                obj.hasSelected();
                expect(obj.selected()).toEqual(true);
            });
        });

        describe('"removeSelected" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('removeSelected')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.removeSelected;

                expect(type).toEqual('function');
            });
            it('Must remove data from selected array', function () {
                obj.selected(['magento', 'magento2']);
                obj.removeSelected('magento');
                expect(obj.selected()).toEqual(['magento2']);
            });
        });

        describe('"isTabKey" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('isTabKey')).toBeDefined();
            });
            it('Check method type', function () {
                var type = typeof obj.isTabKey;

                expect(type).toEqual('function');
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.hasSelected();

                expect(type).toEqual('boolean');
            });
            it('Must return false if pressed not tab key', function () {
                var event = {keyCode: 9};

                expect(obj.isTabKey(event)).toEqual(true);
            });
            it('Must return true if pressed tab key', function () {
                var event = {keyCode: 33};

                expect(obj.isTabKey(event)).toEqual(false);
            });
        });

        describe('"initOptions" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('initOptions')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.initOptions;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                expect(obj.initOptions()).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.initOptions();

                expect(type).toEqual('object');
            });
            it('Check "this.optionsConfig.options" property', function () {
                obj.optionsConfig.options = null;
                obj.initOptions();
                expect(obj.optionsConfig.options).toEqual([]);
            });
        });

        describe('"cleanHoveredElement" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('cleanHoveredElement')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.cleanHoveredElement;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                expect(obj.cleanHoveredElement()).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.cleanHoveredElement();

                expect(type).toEqual('object');
            });
            it('Check changes "this.hoverElIndex" observe variable if listVisible is true', function () {
                obj.hoverElIndex(5);
                obj.listVisible(true);
                obj.cleanHoveredElement();
                expect(obj.hoverElIndex()).toEqual(5);
            });
            it('Check changes "this.hoverElIndex" observe variable if listVisible is false', function () {
                obj.hoverElIndex(5);
                obj.listVisible(false);
                obj.cleanHoveredElement();
                expect(obj.hoverElIndex()).toEqual(null);
            });
            it('Check execution "cleanHoveredElement" method if this.hoverElIndex is null', function () {
                obj.hoverElIndex(null);
                obj.listVisible(false);
                obj.cleanHoveredElement();
                expect(obj.hoverElIndex()).toEqual(null);
            });
        });
        describe('"isSelected" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('isSelected')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.isSelected;

                expect(type).toEqual('function');
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.isSelected();

                expect(type).toEqual('boolean');
            });
            it('Must return true if array "selected" has value', function () {
                obj.selected(['magento']);
                expect(obj.isSelected('magento')).toEqual(true);
            });
            it('Must return false if array "selected" has not value', function () {
                obj.selected(['magento']);
                expect(obj.isSelected('magentoTwo')).toEqual(false);
            });
        });
        describe('"isHovered" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('isHovered')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.isHovered;

                expect(type).toEqual('function');
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.isHovered();

                expect(type).toEqual('boolean');
            });
            it('Must return false if "hoverElIndex" does not equal value', function () {
                obj.hoverElIndex(1);
                expect(obj.isHovered(2)).toEqual(false);
            });
        });
        describe('"toggleListVisible" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('toggleListVisible')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.toggleListVisible;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                expect(obj.cleanHoveredElement()).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.toggleListVisible();

                expect(type).toEqual('object');
            });
            it('Must be false if "listVisible" is true', function () {
                obj.listVisible(true);
                obj.toggleListVisible();
                expect(obj.listVisible()).toEqual(false);
            });
            it('Must be true if "listVisible" is false', function () {
                obj.listVisible(false);
                obj.toggleListVisible();
                expect(obj.listVisible()).toEqual(true);
            });
        });
        describe('"toggleOptionSelected" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('toggleOptionSelected')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.toggleOptionSelected;

                expect(type).toEqual('function');
            });
            it('Check returned value if method called without arguments', function () {
                var data = {label: 'label'};

                expect(obj.toggleOptionSelected(data)).toBeDefined();
            });
            it('Check returned value type if method called without arguments', function () {
                var data = {label: 'label'},
                    type = typeof obj.toggleOptionSelected(data);

                expect(type).toEqual('object');
            });
            it('Transmitted value must be in "selected" array if "selected" array has not this value', function () {
                var data = {label: 'label'};

                obj.selected(['magento']);
                obj.toggleOptionSelected(data);
                expect(obj.selected()[1]).toEqual(data.label);
            });
            it('Transmitted value must be removed in "selected" array if "selected" array has this value', function () {
                var data = {label: 'label'};

                obj.selected(['label']);
                obj.toggleOptionSelected(data);
                expect(obj.selected()).toEqual([]);
            });
        });
        describe('"onHoveredIn" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('onHoveredIn')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.onHoveredIn;

                expect(type).toEqual('function');
            });
            it('Observe variable "hoverElIndex" must have transmitted value', function () {
                obj.onHoveredIn({}, 5);
                expect(obj.hoverElIndex()).toEqual(5);
            });
        });
        describe('"onHoveredOut" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('onHoveredOut')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.onHoveredOut;

                expect(type).toEqual('function');
            });
            it('Observe variable "hoverElIndex" must be null', function () {
                obj.onHoveredOut();
                expect(obj.hoverElIndex()).toEqual(null);
            });
        });
        describe('"onFocusIn" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('onFocusIn')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.onFocusIn;

                expect(type).toEqual('function');
            });
            it('Observe variable "multiselectFocus" must be true', function () {
                obj.onFocusIn();
                expect(obj.multiselectFocus()).toEqual(true);
            });
        });
        describe('"onFocusOut" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('onFocusOut')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.onFocusOut;

                expect(type).toEqual('function');
            });
            it('Observe variable "multiselectFocus" must be false', function () {
                obj.onFocusOut();
                expect(obj.multiselectFocus()).toEqual(false);
            });
            it('Observe variable "listVisible" must be false', function () {
                obj.listVisible(true);
                obj.onFocusOut();
                expect(obj.listVisible()).toEqual(false);
            });
        });
        describe('"enterKeyHandler" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('enterKeyHandler')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.enterKeyHandler;

                expect(type).toEqual('function');
            });
            it('Observe variable "listVisible" must be true', function () {
                obj.listVisible(false);
                obj.enterKeyHandler();
                expect(obj.listVisible()).toEqual(true);
            });
            it('if list visible is true, method "toggleOptionSelected" must be called with argument', function () {
                obj.listVisible(true);
                obj.hoverElIndex(0);
                obj.options(['magento']);
                obj.toggleOptionSelected = jasmine.createSpy();
                obj.enterKeyHandler();
                expect(obj.toggleOptionSelected).toHaveBeenCalledWith('magento');
            });
        });
        describe('"escapeKeyHandler" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('escapeKeyHandler')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.escapeKeyHandler;

                expect(type).toEqual('function');
            });
            it('if list visible is true, method "setListVisible" must be called with argument "false"', function () {
                var setListVisibleCache = obj.setListVisible;

                obj.listVisible(true);
                obj.setListVisible = jasmine.createSpy();
                obj.escapeKeyHandler();
                expect(obj.setListVisible).toHaveBeenCalledWith(false);
                obj.setListVisible = setListVisibleCache;
            });
        });
        describe('"pageDownKeyHandler" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('pageDownKeyHandler')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.pageDownKeyHandler;

                expect(type).toEqual('function');
            });
            it('If "hoverElIndex" is null - "hoverElIndex" must be 0', function () {
                obj.hoverElIndex(null);
                obj.pageDownKeyHandler();
                expect(obj.hoverElIndex()).toEqual(0);
            });
            it('If "hoverElIndex" is number - "hoverElIndex" must be number + 1', function () {
                obj.hoverElIndex(1);
                obj.options(['one', 'two', 'three']);
                obj.pageDownKeyHandler();
                expect(obj.hoverElIndex()).toEqual(2);
            });
            it('If "hoverElIndex" is number and number === options length -1, "hoverElIndex" must be 0', function () {
                obj.hoverElIndex(1);
                obj.options(['one', 'two']);
                obj.pageDownKeyHandler();
                expect(obj.hoverElIndex()).toEqual(0);
            });
        });
        describe('"pageUpKeyHandler" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('pageUpKeyHandler')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.pageUpKeyHandler;

                expect(type).toEqual('function');
            });
            it('If "hoverElIndex" is null - "hoverElIndex" must be option length -1', function () {
                obj.hoverElIndex(null);
                obj.options(['one', 'two']);
                obj.pageUpKeyHandler();
                expect(obj.hoverElIndex()).toEqual(1);
            });
            it('If "hoverElIndex" is 0 - "hoverElIndex" must be option length -1', function () {
                obj.hoverElIndex(0);
                obj.options(['one', 'two']);
                obj.pageUpKeyHandler();
                expect(obj.hoverElIndex()).toEqual(1);
            });
            it('If "hoverElIndex" is number - "hoverElIndex" must be number - 1', function () {
                obj.hoverElIndex(2);
                obj.options(['one', 'two']);
                obj.pageUpKeyHandler();
                expect(obj.hoverElIndex()).toEqual(1);
            });
        });
        describe('"keydownSwitcher" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('keydownSwitcher')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.keydownSwitcher;

                expect(type).toEqual('function');
            });
            it('If press enter key must be called "enterKeyHandler" method', function () {
                obj.enterKeyHandler = jasmine.createSpy();
                obj.keydownSwitcher({}, {keyCode: 13});
                expect(obj.enterKeyHandler).toHaveBeenCalled();
            });
            it('If press escape key must be called "escapeKeyHandler" method', function () {
                obj.escapeKeyHandler = jasmine.createSpy();
                obj.keydownSwitcher({}, {keyCode: 27});
                expect(obj.escapeKeyHandler).toHaveBeenCalled();
            });
            it('If press space key must be called "enterKeyHandler" method', function () {
                obj.enterKeyHandler = jasmine.createSpy();
                obj.keydownSwitcher({}, {keyCode: 32});
                expect(obj.enterKeyHandler).toHaveBeenCalled();
            });
            it('If press pageup key must be called "pageUpKeyHandler" method', function () {
                obj.pageUpKeyHandler = jasmine.createSpy();
                obj.keydownSwitcher({}, {keyCode: 38});
                expect(obj.pageUpKeyHandler).toHaveBeenCalled();
            });
            it('If press pagedown key must be called "pageDownKeyHandler" method', function () {
                obj.pageDownKeyHandler = jasmine.createSpy();
                obj.keydownSwitcher({}, {keyCode: 40});
                expect(obj.pageDownKeyHandler).toHaveBeenCalled();
            });
            it('If object have not transmitted property must returned true', function () {
                expect(obj.keydownSwitcher({}, {keyCode: 88})).toEqual(true);
            });
        });
        describe('"setCaption" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('setCaption')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.setCaption;

                expect(type).toEqual('function');
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.setCaption();

                expect(type).toEqual('string');
            });
            it('Check returned value if selected array length is 1', function () {
                obj.selected(['one']);

                expect(obj.setCaption()).toEqual('one');
            });
            it('Check returned value if selected array length more then 1', function () {
                obj.selected(['one', 'two']);

                expect(obj.setCaption()).toEqual('2 ' + obj.selectedPlaceholders.lotPlaceholders);
            });
            it('Check returned value if selected array length is 0', function () {
                obj.selected([]);

                expect(obj.setCaption()).toEqual(obj.selectedPlaceholders.defaultPlaceholder);
            });
        });
        describe('"setValue" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('setValue')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.setValue;

                expect(type).toEqual('function');
            });
            it('Check "this.value". this.value must equal this.selected', function () {
                var array = ['one', 'two', 'three'];

                obj.selected(array);
                obj.setValue();
                expect(obj.value()).toEqual(array);
            });
        });
        describe('"keyDownHandlers" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('keyDownHandlers')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.keyDownHandlers;

                expect(type).toEqual('function');
            });
        });
        describe('"setListVisible" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('setListVisible')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.setListVisible;

                expect(type).toEqual('function');
            });
            it('Check "this.listVisible" if transmitted argument is false', function () {
                obj.listVisible(true);
                obj.setListVisible(false);
                expect(obj.listVisible()).toEqual(false);
            });
            it('Check "this.listVisible" if transmitted argument is true', function () {
                obj.listVisible(false);
                obj.setListVisible(true);
                expect(obj.listVisible()).toEqual(true);
            });
        });
        describe('"getPreview" method', function () {
            it('Check for defined ', function () {
                expect(obj.hasOwnProperty('getPreview')).toBeDefined();
            });
            it('Check answer type', function () {
                var type = typeof obj.getPreview;

                expect(type).toEqual('function');
            });
            it('Check returned value type if method called without arguments', function () {
                var type = typeof obj.getPreview();

                expect(type).toEqual('string');
            });
            it('Must return concat string with values from "this.selected" array', function () {
                obj.selected(['one', 'two']);

                expect(obj.getPreview()).toEqual('one,two');
            });
        });
    });
});
