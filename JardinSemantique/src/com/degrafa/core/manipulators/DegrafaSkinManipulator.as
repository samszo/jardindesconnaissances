////////////////////////////////////////////////////////////////////////////////
// Copyright (c) 2008 Jason Hawryluk, Juan Sanchez, Andy McIntosh, Ben Stucki 
// and Pavan Podila.
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in
// all copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
// THE SOFTWARE.
////////////////////////////////////////////////////////////////////////////////
package com.degrafa.core.manipulators{	
	
	import com.degrafa.core.DegrafaObject;
	import com.degrafa.core.IGraphicSkin;
	import com.degrafa.core.collections.GraphicSkinCollection;
	
	import flash.display.DisplayObject;
	import flash.display.DisplayObjectContainer;
	import flash.events.Event;
	
	import mx.events.FlexEvent;
	import mx.events.PropertyChangeEvent;
	
	
	[Bindable]
	/**
	* The DegrafaSkinManipulator allows one to tie in and manipulate the specified 
	* IGraphicSkin for an object. Degrafa Geometry, fills and strokes can then be 
	* manipulated and bound to like you would do with any other degrafa graphic 
	* or geometry object.
	**/
	public class DegrafaSkinManipulator extends DegrafaObject{			
		
		private var _target:DisplayObjectContainer;
		/**
		* Target UI Object for this manipulator
		**/
		public function get target():DisplayObjectContainer
		{
			return _target;
		}
		public function set target(value:DisplayObjectContainer):void{
			if (!value){return;}
			_target = value;
			
			_target.addEventListener("added",onChildAdded);
			
			//add the creation complete handler
			_target.addEventListener(FlexEvent.CREATION_COMPLETE,onTargetCreationComplete);
			
		}
		
		/**
		* Return the requested skin if available by name.
		**/
		public function getSkinByName(value:String):IGraphicSkin{
			for each (var item:IGraphicSkin in skinsCollection.items){
				if (item.name == value){
					return item;
				}
			}
			return null;
		}
		
		/**
		* Processes skins as they are instantiated.
		**/
		private function onChildAdded(event:Event):void{
			//add item to the skins collection if not already present
			if(event.target is IGraphicSkin){
				var exists:Boolean;
				for each (var item:IGraphicSkin in skinsCollection.items){
					if (item == IGraphicSkin(event.target)){
						exists = true;
					}
				}
				if(!exists){
					skinsCollection.addItem(IGraphicSkin(event.target));
				}
			}
		}
		
		/**
		* Sets up this manipulator.
		**/
		private function onTargetCreationComplete(event:FlexEvent):void{
			
			var i:int;
			
			//loop through the children and get the first IGraphicSkin that matches	
			//the targetSkinClass.		
			if(event.currentTarget is DisplayObject){
					if(event.currentTarget.hasOwnProperty("rawChildren")){
						if(targetSkinClass){
							for (i=0;i<event.currentTarget.rawChildren.numChildren;i++){
								if (event.currentTarget.rawChildren.getChildAt(i) is targetSkinClass && 
								event.currentTarget.rawChildren.getChildAt(i) is IGraphicSkin){
									
									_currentSkin=event.currentTarget.rawChildren.getChildAt(i);
									
								}	 
							}
						}
					}
					else{
						for (i=0;i<event.currentTarget.numChildren;i++){
							
							var x:Object = event.currentTarget.getChildAt(i);
							
							if (event.currentTarget.getChildAt(i) is targetSkinClass && 
							event.currentTarget.getChildAt(i) is IGraphicSkin){
								_currentSkin=event.currentTarget.getChildAt(i);
							}	 
						}
					}
				}
			}
		

		
		
		private var _targetSkinClass:Class;
		/**
		* Desired IGraphicSkin object we wish to tie into.
		**/
		public function set targetSkinClass(value:Class):void{
			if (!value){return;}
			_targetSkinClass = value;
		}
		public function get targetSkinClass():Class{
			return _targetSkinClass;
		}
				
		
		
		private var _currentSkin:IGraphicSkin;
		/**
		* Desired IGraphicSkin object for this hook
		**/
		public function get currentSkin():IGraphicSkin{
			return _currentSkin;
		}
		public function set currentSkin(value:IGraphicSkin):void{
			if (!value){return;}
			
			if(_currentSkin !=value){
				_currentSkin.removeEventListener(PropertyChangeEvent.PROPERTY_CHANGE,propertyChangeHandler);
			}
			
			_currentSkin = value;
			_currentSkin.addEventListener(PropertyChangeEvent.PROPERTY_CHANGE,propertyChangeHandler);
			
		}
		
		
		
		
		private var _skins:GraphicSkinCollection;
		/**
		* Access to an array of IGeometry items added as children
		* to the skin.
		**/
		public function get skinsCollection():GraphicSkinCollection{
			if(!_skins){
				_skins = new GraphicSkinCollection();
				_skins.addEventListener(PropertyChangeEvent.PROPERTY_CHANGE,propertyChangeHandler);
			
			}
			return _skins;
		}
		

		/**
		* Principle event handler for any property changes to a 
		* skin object or it's child objects.
		**/
		private function propertyChangeHandler(event:PropertyChangeEvent):void{
			dispatchEvent(event);
		}
		
		
	}
}