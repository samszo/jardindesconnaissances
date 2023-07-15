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
	
	import com.degrafa.core.IGraphicsFill;
	import com.degrafa.core.IGraphicsStroke;
	import flash.display.DisplayObject;
	import flash.geom.Rectangle;

	import flash.display.DisplayObjectContainer;
	import flash.display.Graphics;
	
	/**
	* Base interface for Degrafa Graphic objects.
	**/
	public interface IGraphic{
		
		function get width():Number
		function set width(value:Number):void
		
		function get height():Number
		function set height(value:Number):void
		
		function get x():Number
		function set x(value:Number):void
		
		function get y():Number
		function set y(value:Number):void
		
		function get name():String 
	    function set name(value:String):void 
				
		function addEventListener(type:String, listener:Function, useCapture:Boolean = false, priority:int = 0, useWeakReference:Boolean = false):void
		function removeEventListener(type:String, listener:Function, useCapture:Boolean = false):void
				
		function get fills():Array
		function set fills(value:Array):void 
		 
		function get strokes():Array
		function set strokes(value:Array):void
						
		function set percentHeight(value:Number):void
		function get percentHeight():Number
		
		function set percentWidth(value:Number):void
		function get percentWidth():Number
		
		function get measuredWidth():Number 
		function get measuredHeight():Number 
						
		function get parent():DisplayObjectContainer
				
		function draw(graphics:Graphics,rc:Rectangle):void
		function endDraw(graphics:Graphics):void
		
		function set target(value:DisplayObjectContainer):void
		function get target():DisplayObjectContainer
		
		function get stroke():IGraphicsStroke
		function set stroke(value:IGraphicsStroke):void
		
		function get fill():IGraphicsFill
		function set fill(value:IGraphicsFill):void
		
	}
}