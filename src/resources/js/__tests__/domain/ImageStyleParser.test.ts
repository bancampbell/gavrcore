import { describe, it, expect } from 'vitest';
import { ImageStyleParser } from '../../domain/services/ImageStyleParser';

describe('ImageStyleParser', () => {
    describe('parse', () => {
        it('returns empty props for empty style', () => {
            expect(ImageStyleParser.parse('')).toEqual({});
        });

        it('parses center align', () => {
            const result = ImageStyleParser.parse('margin-left: auto; margin-right: auto; display: block');
            expect(result.align).toBe('center');
        });

        it('parses left align', () => {
            const result = ImageStyleParser.parse('margin-left: 0; margin-right: auto; display: block');
            expect(result.align).toBe('left');
        });

        it('parses right align', () => {
            const result = ImageStyleParser.parse('margin-left: auto; margin-right: 0; display: block');
            expect(result.align).toBe('right');
        });

        it('parses float left with margin', () => {
            const result = ImageStyleParser.parse('float: left; margin-right: 15px');
            expect(result.float).toBe('left');
            expect(result.margin).toBe(15);
        });

        it('parses float right with margin', () => {
            const result = ImageStyleParser.parse('float: right; margin-left: 20px');
            expect(result.float).toBe('right');
            expect(result.margin).toBe(20);
        });

        it('parses width and height', () => {
            const result = ImageStyleParser.parse('width: 300px; height: 200px');
            expect(result.width).toBe(300);
            expect(result.height).toBe(200);
        });

        it('parses combined style', () => {
            const result = ImageStyleParser.parse(
                'width: 400px; height: 300px; display: block; margin-left: auto; margin-right: auto'
            );
            expect(result.width).toBe(400);
            expect(result.height).toBe(300);
            expect(result.align).toBe('center');
        });
    });

    describe('build', () => {
        it('builds basic style string', () => {
            const style = ImageStyleParser.build({ width: 200, height: 150 });
            expect(style).toContain('width: 200px');
            expect(style).toContain('height: 150px');
            expect(style).toContain('display: block');
        });

        it('builds center-aligned style', () => {
            const style = ImageStyleParser.build({ align: 'center' });
            expect(style).toContain('margin-left: auto');
            expect(style).toContain('margin-right: auto');
        });

        it('builds left-aligned style', () => {
            const style = ImageStyleParser.build({ align: 'left' });
            expect(style).toContain('margin-left: 0');
            expect(style).toContain('margin-right: auto');
        });

        it('builds right-aligned style', () => {
            const style = ImageStyleParser.build({ align: 'right' });
            expect(style).toContain('margin-left: auto');
            expect(style).toContain('margin-right: 0');
        });

        it('builds float left with margin', () => {
            const style = ImageStyleParser.build({ float: 'left', margin: 10 });
            expect(style).toContain('float: left');
            expect(style).toContain('margin-right: 10px');
        });

        it('builds float right with margin', () => {
            const style = ImageStyleParser.build({ float: 'right', margin: 15 });
            expect(style).toContain('float: right');
            expect(style).toContain('margin-left: 15px');
        });

        it('parse then build returns identical props', () => {
            const original = 'width: 300px; display: block; margin-left: auto; margin-right: auto';
            const props = ImageStyleParser.parse(original);
            const rebuilt = ImageStyleParser.build(props);
            const reparsed = ImageStyleParser.parse(rebuilt);
            expect(reparsed).toEqual(props);
        });
    });
});
