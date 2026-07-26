export class Selection {
    public readonly from: number;
    public readonly to: number;
    public readonly text: string;

    constructor(from: number, to: number, text: string) {
        this.from = from;
        this.to = to;
        this.text = text;
    }

    get isEmpty(): boolean {
        return this.from === this.to;
    }
}
