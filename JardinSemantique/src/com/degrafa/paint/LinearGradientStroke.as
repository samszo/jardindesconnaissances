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
	
	import flash.display.Graphics;
	import flash.geom.Matrix;
	import flash.geom.Rectangle;
	
	[Exclude(name="focalPointRatio", kind="property")]
	[Bindable(event="propertyChange")]
	
	/**
	* The linear gradient stroke class lets you specify a gradient filled stroke.
	* 
	* @see mx.graphics.LinearGradientStroke 
	* @see http://samples.degrafa.com/LinearGradientStroke/LinearGradientStroke.html
	**/
	public class LinearGradientStroke extends GradientStroke{
		
		public function LinearGradientStroke():void{
			super();
			super.gradientType = "linear";
		}
		
		/**
 		* Applies the properties to the specified Graphics object.
 		* 
 		* @see mx.graphics.LinearGradientStroke
 		* 
 		* @param graphics The current context to draw to.
		* @param rc A Rectangle object used for stroke bounds. 
 		**/
		override public function apply(graphics:Graphics,rc:Rectangle):void{
			//set the base line style properties
			super.apply(graphics,rc);			
		}
		
	}
}