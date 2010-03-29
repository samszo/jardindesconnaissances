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
package com.degrafa.paint{
	
	import com.degrafa.core.IGraphicsFill;
	import flash.display.Graphics;
	import flash.geom.Matrix;
	import flash.geom.Rectangle;
	
	[Exclude(name="focalPointRatio", kind="property")]
	
	[Bindable(event="propertyChange")]
	
	/**
	* The linear gradient fill class lets you specify a gradient fill.
	* 
	* @see mx.graphics.LinearGradient 
	* @see http://samples.degrafa.com/LinearGradientFill/LinearGradientFill.html	 
	**/
	public class LinearGradientFill extends GradientFill implements IGraphicsFill{
		
		public function LinearGradientFill(){
			super();
			super.gradientType="linear";
		}
		
		private var _x:Number=0;
		/**
		* The x-axis coordinate of the upper left point of the fill rectangle. If not specified 
		* a default value of 0 is used.
		* 
		* Note: Not yet implemented. 
		**/
		public function get x():Number{
			return _x;
		}
		public function set x(value:Number):void{
			if(_x != value){
				_x = value;
			}
		}
		
		
		private var _y:Number=0;
		/**
		* The y-axis coordinate of the upper left point of the fill rectangle. If not specified 
		* a default value of 0 is used.
		* 
		* Note: Not yet implemented. 
		**/
		public function get y():Number{
			return _y;
		}
		public function set y(value:Number):void{
			if(_y != value){
				_y = value;
			}
		}
		
						
		private var _width:Number=0;
		/**
		* The width of the fill rectangle.
		* 
		* Note: Not yet implemented. 
		**/
		public function get width():Number{
			return _width;
		}
		public function set width(value:Number):void{
			if(_width != value){
				_width = value;
			}
		}
		
		
		private var _height:Number=0;
		/**
		* The height of the fill rectangle.
		*
		* Note: Not yet implemented.
		**/
		public function get height():Number{
			return _height;
		}
		public function set height(value:Number):void{
			if(_height != value){
				_height = value;
			}
		}
		
		
			
		/**
		* Ends the fill for the graphics context.
		* 
		* @param graphics The current context being drawn to.
		**/
		override public function end(graphics:Graphics):void
		{
			super.end(graphics);
		}
		
		/**
		* Begins the fill for the graphics context.
		* 
		* @param graphics The current context to draw to.
		* @param rc A Rectangle object used for fill bounds.  
		**/
		override public function begin(graphics:Graphics, rc:Rectangle):void
		{
			super.begin(graphics,rc);
		}
		
		
	}
}