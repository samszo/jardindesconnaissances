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
package com.degrafa.geometry.repeaters{
	
	import com.degrafa.core.IDegrafaObject;
	import com.degrafa.geometry.Geometry;
	import com.degrafa.IGeometry;
	
	[Bindable]
	/**
 	*  The Repeater element repeats a series of objects using the specified 
 	*  count, offsetX, and offsetY. It is the base class for all repeater elements. 
 	*  A repeater will repeat it’s geometry for a specified count and progressively 
 	*  add geometry using offsets from the previous geometric item.
 	*  
 	*  @see http://samples.degrafa.com/Repeater_Element.html
 	*  
 	**/
	public class Repeater extends Geometry implements IGeometry, IDegrafaObject{
		
		private var _count:Number=0;
		/**
		* The number of times to repeat the geometry.
		**/
		public function get count():Number{
			return _count;
		}
		public function set count(value:Number):void{
			if(_count != value){
				_count = value;
				invalidated =true;
			}
			
		}
						
		private var _offsetX:Number=0;
		/**
		* The offset of the x-axis of each geometry object.
		**/
		public function get offsetX():Number{
			return _offsetX;
		}
		public function set offsetX(value:Number):void{
			if(_offsetX != value){
				_offsetX = value;
				invalidated =true;
			}
			
		}

		private var _offsetY:Number=0;
		/**
		* The offset of the y-axis of each geometry object.
		**/
		public function get offsetY():Number{
			return _offsetY;
		}
		public function set offsetY(value:Number):void{
			if(_offsetY != value){
				_offsetY = value;
				invalidated =true;
			}
		}
		
						
	}
}