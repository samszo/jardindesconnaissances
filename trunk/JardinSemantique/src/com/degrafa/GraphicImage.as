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

package com.degrafa{
	
	import flash.display.DisplayObject;
	import flash.display.Graphics;
	import flash.display.Loader;
	import flash.events.Event;
	import flash.geom.Rectangle;
	import flash.net.URLRequest;
	
	[Bindable(event="propertyChange")]
	/**
	* GraphicImage Enables support for images to be added to compositions. 
	**/
	public class GraphicImage extends Graphic implements IGraphic, IGeometry{
		
		public function GraphicImage(){
			super();
		}
		
		/**
		* Data is required for the IGeometry interface and has no effect here.
		* @private  
		**/	
		public function get data():String{return "";}
		public function set data(value:String):void{}
		
		private var loader:Loader;
		private var _source:Object;
		
		/**
		* The URL, class or string name of a class to load as the content
		**/
		public function get source():Object{
			return _source;
		}
		public function set source(value:Object):void{
			_source = value;
						
			var newClass:Class;
			
			if (_source is Class)
			{
				newClass = Class(_source);
			}
			else if (_source is String)
			{	
				//treat as a url so need a loader
				loader = new Loader();
				var urlRequest:URLRequest = new URLRequest(String(_source));
				loader.load(urlRequest);
				loader.contentLoaderInfo.addEventListener(Event.COMPLETE, onLoaded);
			}
			
			if(newClass){
				var child:DisplayObject;
				child = new newClass();
       			addChild(child);
   			}
		}
		
		/**
		* Called when the image has been successfully loaded.
		**/
		private function onLoaded(event:Event):void{
		    addChild(event.target.content);
		    loader.contentLoaderInfo.removeEventListener(Event.COMPLETE, onLoaded);
		}
		
		/**
		* draw is required for the IGeometry interface and has no effect here.
		* @private  
		**/	
		override public function draw(graphics:Graphics,rc:Rectangle):void{}
		
		/**
		* endDraw is required for the IGeometry interface and has no effect here.
		* @private  
		**/
		override public function endDraw(graphics:Graphics):void{}
		
				
	}
}