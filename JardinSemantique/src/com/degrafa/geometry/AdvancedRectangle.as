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
package com.degrafa.geometry
{
	import com.degrafa.IGeometry;
	
	import flash.display.DisplayObject;
	import flash.display.Graphics;
	import flash.geom.Rectangle;
	
	import mx.graphics.IFill;
	import mx.graphics.IStroke;
	import mx.graphics.SolidColor;
	import mx.graphics.Stroke;
	
	/**
	 * Used by the CSSSkin for graphics rendering.
	 */
	public class AdvancedRectangle extends Geometry implements IGeometry
	{
		
		public var backgroundFill:IFill;
		
		public var leftWidth:Number;
		public var topWidth:Number;
		public var rightWidth:Number;
		public var bottomWidth:Number;
		
		public var leftFill:IFill;
		public var topFill:IFill;
		public var rightFill:IFill;
		public var bottomFill:IFill;
		
		public var topLeftRadiusX:Number;
		public var topLeftRadiusY:Number;
		public var topRightRadiusX:Number;
		public var topRightRadiusY:Number;
		public var bottomLeftRadiusX:Number;
		public var bottomLeftRadiusY:Number;
		public var bottomRightRadiusX:Number;
		public var bottomRightRadiusY:Number;
		
		public var topLeftFill:IFill;
		public var topRightFill:IFill;
		public var bottomLeftFill:IFill;
		public var bottomRightFill:IFill;
		
		
		
		override public function draw(graphics:Graphics, rc:Rectangle):void {
			
			// test for simpler drawing methods
			var isRoundRectComplex:Boolean = topWidth == rightWidth == bottomWidth == leftWidth && topLeftRadiusX == topLeftRadiusY && topRightRadiusX == topRightRadiusY && bottomLeftRadiusX == bottomLeftRadiusY && bottomRightRadiusX == bottomRightRadiusY && isEquivalentSolidFill([topFill,rightFill,bottomFill,leftFill]);
			var isRoundRect:Boolean = isRoundRectComplex && topLeftRadiusX == topRightRadiusX == bottomLeftRadiusX == bottomRightRadiusX;
			var isRect:Boolean = isRoundRect && topLeftRadiusX == 0;
			
			if(isRect || isRoundRect || isRoundRectComplex) {
				var stroke:IStroke = convertSolidColorToStroke(topFill as SolidColor, topWidth);
				stroke.apply(graphics);
				backgroundFill.begin(graphics, rc);
				if(isRect) {
					graphics.drawRect(rc.x, rc.y, rc.width, rc.height);
				} else if(isRoundRect) {
					graphics.drawRoundRect(rc.x, rc.y, rc.width, rc.height, topLeftRadiusX, topLeftRadiusY);
				} else if(isRoundRectComplex) {
					graphics.drawRoundRectComplex(rc.x, rc.y, rc.width, rc.height, topLeftRadiusX, topRightRadiusX, bottomLeftRadiusX, bottomRightRadiusX);
				}
				backgroundFill.end(graphics);
			} else {
				drawLeftBorder(graphics, rc);
				drawTopLeftRadius(graphics, rc);
				drawTopBorder(graphics, rc);
				drawTopRightRadius(graphics, rc);
				drawRightBorder(graphics, rc);
				drawBottomLeftRadius(graphics, rc);
				drawBottomBorder(graphics, rc);
				drawBottomRightRadius(graphics, rc);
				drawBackground(graphics, rc);
			}
		}
		
		//**************************************************************************
		// Drawing Functions
		//**************************************************************************
		//private var fill:IFill; // temp
		//private var weight:Number = 3; // temp
		
		private function drawLeftBorder(graphics:Graphics, rectangle:Rectangle):void {
			if(leftFill != null) {
				var rc:Rectangle = new Rectangle(0, topLeftRadiusY, leftWidth, rectangle.height - topLeftRadiusY - bottomLeftRadiusY);
				graphics.lineStyle(0, 0, 0);
				//fill = new SolidColor(leftFill);
				leftFill.begin(graphics, rc);
				graphics.moveTo(0, topLeftRadiusY); // top outside
				graphics.lineTo(leftWidth, Math.max(topLeftRadiusY, topWidth)); // top inside
				graphics.lineTo(leftWidth, rectangle.height - Math.max(bottomLeftRadiusY, bottomWidth)); // bottom inside
				graphics.lineTo(0, rectangle.height - bottomLeftRadiusY); // bottom outside
				graphics.lineTo(0, topLeftRadiusY); // top outside
				leftFill.end(graphics);
			}
		}
		
		private function drawTopBorder(graphics:Graphics, rectangle:Rectangle):void {
			if(topFill != null) {
				var rc:Rectangle = new Rectangle(topLeftRadiusX, 0, rectangle.width - topLeftRadiusX - topRightRadiusX, topWidth);
				graphics.lineStyle(0, 0, 0);
				//fill = new SolidColor(0xFF0000);
				topFill.begin(graphics, rc);
				graphics.moveTo(topLeftRadiusX, 0);
				graphics.lineTo(rectangle.width  - topRightRadiusX, 0);
				graphics.lineTo(rectangle.width - Math.max(topRightRadiusX, rightWidth), topWidth);
				graphics.lineTo(Math.max(topLeftRadiusX, leftWidth), topWidth);
				graphics.lineTo(topLeftRadiusX, 0);
				topFill.end(graphics);
			}
		}
		
		private function drawRightBorder(graphics:Graphics, rectangle:Rectangle):void {
			if(rightFill != null) {
				var rc:Rectangle = new Rectangle(0, topRightRadiusY, rightWidth, rectangle.height - topRightRadiusY - bottomRightRadiusY);
				graphics.lineStyle(0, 0, 0);
				//fill = new SolidColor(0x00FF00);
				rightFill.begin(graphics, rc);
				graphics.moveTo(rectangle.width, Math.max(topRightRadiusY, topWidth)); // top outside
				graphics.lineTo(rectangle.width, rectangle.height - bottomRightRadiusY); // bottom outside
				graphics.lineTo(rectangle.width - rightWidth, rectangle.height - Math.max(bottomRightRadiusY, bottomWidth));
				graphics.lineTo(rectangle.width - rightWidth, Math.max(topRightRadiusY, topWidth));
				graphics.lineTo(rectangle.width, Math.max(topRightRadiusY, topWidth)); // top outside
				rightFill.end(graphics);
			}
		}
		
		private function drawBottomBorder(graphics:Graphics, rectangle:Rectangle):void {
			if(bottomFill != null) {
				var rc:Rectangle = new Rectangle(bottomLeftRadiusX, 0, rectangle.width - bottomLeftRadiusX - bottomRightRadiusX, bottomWidth);
				graphics.lineStyle(0, 0, 0);
				//fill = new SolidColor(0x0000FF);
				bottomFill.begin(graphics, rc);
				graphics.moveTo(Math.max(bottomLeftRadiusX, leftWidth), rectangle.height - bottomWidth); // left inside
				graphics.lineTo(rectangle.width - Math.max(bottomRightRadiusX, rightWidth), rectangle.height - bottomWidth); // right inside
				graphics.lineTo(rectangle.width - bottomRightRadiusX, rectangle.height); // right outside
				graphics.lineTo(bottomLeftRadiusX, rectangle.height); // left outside
				graphics.lineTo(Math.max(bottomLeftRadiusX, leftWidth), rectangle.height - bottomWidth); // left inside
				graphics.endFill();
				bottomFill.end(graphics);
			}
		}
		
		private function drawTopLeftRadius(graphics:Graphics, rc:Rectangle):void {
			// draw top left curve
			if(topLeftRadiusX > 0){
				//matrix = new Matrix();
				//matrix.createGradientBox(border.topLeft.x, border.topLeft.y, (-45/180)*Math.PI, 0, 0); 
				//graphics.beginGradientFill("linear", [borderLeftColor, borderTopColor], [1, 1], [0, 0xFF], matrix);
				topLeftFill.begin(graphics, rc);
				graphics.moveTo(0, topLeftRadiusY);
				graphics.curveTo(0, 0, topLeftRadiusX, 0);
				graphics.lineTo(Math.max(topLeftRadiusX, leftWidth), topWidth);
				graphics.curveTo(leftWidth, topWidth, leftWidth, Math.max(topLeftRadiusY, topWidth));
				graphics.lineTo(0, topLeftRadiusY);
				topLeftFill.end(graphics);
				//graphics.endFill();
			}
		}
		
		private function drawTopRightRadius(graphics:Graphics, rectangle:Rectangle):void {
			// draw top right curve
			if(topRightRadiusX > 0){
				//matrix = new Matrix();
				//matrix.createGradientBox(border.topRight.x, border.topRight.y, (45/180)*Math.PI, unscaledWidth - border.topRight.x, 0);
				//graphics.beginGradientFill("linear", [borderTopColor, borderRightColor], [1, 1], [0, 0xFF], matrix);
				var trc:Rectangle = new Rectangle(rectangle.width - Math.max(rightWidth, topRightRadiusX), 0, Math.max(topRightRadiusX, rightWidth), Math.max(topRightRadiusY, topWidth));
				topRightFill.begin(graphics, trc);
				graphics.moveTo(rectangle.width - topRightRadiusX, 0);
				graphics.curveTo(rectangle.width, 0, rectangle.width,  Math.max(topRightRadiusY, topWidth));
				graphics.lineTo(rectangle.width - rightWidth, Math.max(topRightRadiusY, topWidth));
				graphics.curveTo(rectangle.width - rightWidth, topWidth, rectangle.width - Math.max(topRightRadiusX, rightWidth), topWidth);
				graphics.lineTo(rectangle.width - Math.max(topRightRadiusX, rightWidth), 0);
				topRightFill.end(graphics);
				//graphics.endFill();
			}
		}
		
		private function drawBottomLeftRadius(graphics:Graphics, rc:Rectangle):void {
			// draw bottom left curve
			if(bottomLeftRadiusX > 0){
				//matrix = new Matrix();
				//matrix.createGradientBox(border.bottomLeft.x, border.bottomLeft.y, (45/180)*Math.PI, 0, unscaledHeight - border.bottomLeft.y); 
				//graphics.beginGradientFill("linear", [borderLeftColor, borderBottomColor], [1, 1], [0, 0xFF], matrix);
				var brc:Rectangle = new Rectangle(0, rc.height - Math.max(bottomWidth, bottomLeftRadiusY), Math.max(leftWidth, bottomLeftRadiusX), Math.max(bottomWidth, bottomLeftRadiusY));
				bottomLeftFill.begin(graphics, brc);
				graphics.moveTo(bottomLeftRadiusX, rc.height);
				graphics.curveTo(0, rc.height, 0, rc.height - bottomLeftRadiusY);
				graphics.lineTo(leftWidth, Math.min(rc.height - bottomLeftRadiusY, rc.height - bottomWidth));
				graphics.curveTo(leftWidth, rc.height - bottomWidth, Math.max(bottomLeftRadiusX, leftWidth), rc.height - bottomWidth);
				graphics.lineTo(bottomLeftRadiusX, rc.height);
				bottomLeftFill.end(graphics);
				//graphics.endFill();
			}
		}
		
		private function drawBottomRightRadius(graphics:Graphics, rc:Rectangle):void {
			// draw bottom right curve
			if(bottomRightRadiusX > 0){
				//matrix = new Matrix();
				//matrix.createGradientBox(border.bottomRight.x, border.bottomRight.y, (-45/180)*Math.PI, unscaledWidth - border.bottomRight.x, unscaledHeight - border.bottomRight.y);
				//graphics.beginGradientFill("linear", [borderBottomColor, borderRightColor], [1, 1], [0, 0xFF], matrix);
				
				bottomRightFill.begin(graphics, rc);
				graphics.moveTo(rc.width, rc.height - bottomRightRadiusY);
				graphics.curveTo(rc.width, rc.height, rc.width - bottomRightRadiusX, rc.height);
				graphics.lineTo(Math.min(rc.width - bottomRightRadiusX, rc.width), rc.height - bottomWidth);
				graphics.curveTo(rc.width - rightWidth, rc.height - bottomWidth, rc.width - rightWidth, Math.min(rc.height - bottomRightRadiusY, rc.height));
				graphics.lineTo(rc.width, rc.height - bottomRightRadiusY);
				bottomRightFill.end(graphics);
				//graphics.endFill();
			}
		}
		
		private function drawBackground(graphics:Graphics, rc:Rectangle):void {
			var brc:Rectangle = new Rectangle(rc.x + leftWidth, rc.y + topWidth, rc.width - (leftWidth + rightWidth), rc.height - (topWidth + bottomWidth));
			// draw background
			backgroundFill.begin(graphics, brc);
			// todo: more optimizing
			
			graphics.moveTo(leftWidth, topWidth + topLeftRadiusY);
			
			// top right
			graphics.lineTo(rc.width - Math.max(rightWidth, topRightRadiusX), topWidth); // top right
			if(topRightRadiusX > 0 && topRightRadiusY < topWidth && topRightRadiusX < rightWidth){
				//graphics.curveTo(rc.width, 0, rc.width - rightWidth, topRightRadiusY);
			} else {
				graphics.lineTo(rc.width - rightWidth, Math.max(topWidth, topRightRadiusY));
			}
			
			// bottom right
			graphics.lineTo(rc.width - rightWidth, rc.height - bottomWidth - bottomRightRadiusY);
			if(bottomRightRadiusX > 0){
				graphics.curveTo(rc.width, rc.height, rc.width - bottomRightRadiusX, rc.height - bottomWidth);
			}
			
			// bottom left
			graphics.lineTo(Math.max(leftWidth, bottomLeftRadiusX), rc.height - bottomWidth);
			if(bottomLeftRadiusX > 0){
				graphics.curveTo(leftWidth, rc.height - bottomWidth, leftWidth, rc.height - Math.max(bottomWidth, bottomLeftRadiusY));
				//Math.max(bottomLeftRadiusX, leftWidth), rc.height - bottomWidth
				// leftWidth, rc.height - bottomWidth - bottomLeftRadiusY
			}
			
			// top left
			graphics.lineTo(leftWidth, topWidth + topLeftRadiusY);
			if(topLeftRadiusX > 0){
				graphics.curveTo(0, 0, leftWidth + topLeftRadiusX, topWidth);
			}
			
			graphics.lineTo(rc.width - Math.max(rightWidth, topRightRadiusX), topWidth); // top right
			
			backgroundFill.end(graphics);
		}
		
		
		//************************************************************
		// Utility Functions
		//************************************************************
		
		private function convertSolidColorToStroke(fill:SolidColor, weight:Number):IStroke {
			if(fill != null) {
				return new Stroke(fill.color, weight, fill.alpha);
			} else {
				return new Stroke(0, 0, 0);
			}
		}
		
		private function isEquivalentSolidFill(fills:Array):Boolean {
			var temp:SolidColor;
			for each(var fill:IFill in fills) {
				if(fill is SolidColor || fill == null) {
					if(temp != null) {
						var solid:SolidColor = fill as SolidColor;
						if(solid.color != temp.color || solid.alpha == temp.alpha) {
							return false;
						}
					}
					temp = fill as SolidColor;
				} else {
					return false;
				}
			}
			return true;
		}
		
	}
}