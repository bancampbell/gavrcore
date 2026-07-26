export class NodePosition {
    public readonly pos: number;
    public readonly nodeType: string;

    constructor(pos: number, nodeType: string) {
        this.pos = pos;
        this.nodeType = nodeType;
    }
}
