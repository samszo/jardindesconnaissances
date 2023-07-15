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
	
	[Bindable(event="propertyChange")]
	
	/**
	* The radial gradient fill class lets you specify a gradient fill that 
	* radiates out from the center of a graphical element.
	* 
	* @see mx.graphics.RadialGradient 
	* @see http://samples.degrafa.com/RadialGradientFill/RadialGradientFill.html	 
	**/
	public class RadialGradientFill extends GradientFill implements IGraphicsFill{
		
		public function RadialGradientFill(){
			super();
			super.gradientType = "radial";
					
		}
		
		private var _cx:Number=0;
		/**
		* The x-axis coordinate of the center of the fill rectangle. If not specified 
		* a default value of 0 is used.
		* 
		* Note: Not yet implemented.
		**/
		public function get cx():Number{
			return _cx;
		}
		public function set cx(value:Number):void{
			if(_cx != value){
				_cx = value;
			}
		}
		
		
		private var _cy:Number=0;
		/**
		* The y-axis coordinate of the center of the fill rectangle. If not specified 
		* a default value of 0 is used.
		* 
		* Note: Not yet implemented.  
		**/
		public function get cy():Number{
			return _cy;
		}
		public function set cy(value:Number):void{
			if(_cy != value){
				_cy = value;
			}
		}
		
		
		private var _radius:Number=0;
		/**
		* The radius of the fill. If not specified a default value of 0 
		* is used.
		* 
		* Note: Not yet implemented. 
		**/
		public function get radius():Number{
			return _radius;
		}
		public function set radius(value:Number):void{
			if(_radius != value){
				_radius = value;
			}
		}
		
		
		/**
		* Ends the fill for the graphics context.
		* 
		* @param graphics The current context being drawn to.
		**/
		override public function end(graphics:Graphics):void{
			super.end(graphics);
		}
		
		/**
		* Begins the fill for the graphics context.
		* 
		* @param graphics The current context to draw to.
		* @param rc A Rectangle object used for fill bounds.  
		**/
		override public function begin(graphics:Graphics, rc:Rectangle):void{
			super.begin(graphics,rc);
		}
		
	}
}