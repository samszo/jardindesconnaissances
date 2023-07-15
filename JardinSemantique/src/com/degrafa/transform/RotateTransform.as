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

package com.degrafa.transform{
	
	import flash.geom.Matrix;
	
	[Bindable]
	/**
	* RotateTransform rotates an object in the two-dimensional plane by specifying an angle 
	* (property angle) and a center point expressed in the coordinate space of 
	* the element being transformed (properties centerX and centerY).
	* 
	* Coming Soon. 
	**/
	public class RotateTransform extends Transform{
		public function RotateTransform(){
			super();
		}
		
		private var _centerX:Number=0;
		/**
		* The center point of the transform along the x-axis.
		**/
		public function get centerX():Number{
			return _centerX;
		}
		public function set centerX(value:Number):void{
			_centerX = value;
		}
		
		private var _centerY:Number=0;
		/**
		* The center point of the transform along the y-axis.
		**/
		public function get centerY():Number{
			return _centerY;
		}
		public function set centerY(value:Number):void{
			_centerY = value;
		}
		
		private var _angle:Number=0;
		/**
		* The rotation angle of the transform.
		**/
		public function get angle():Number{
			return _angle;
		}
		public function set angle(value:Number):void{
			_angle = value;
		}
		
		public function apply(matrix:Matrix):void{
			matrix.tx -= _centerX;
			matrix.ty -= _centerY;
			matrix.rotate(angle*(Math.PI/180));
			matrix.tx += _centerX;
			matrix.ty += _centerY;
		}
		
		
	}
}