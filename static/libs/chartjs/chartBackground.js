const changeBackgroundPlugin = {
    id: 'custom_canvas_background_color',
    beforeDraw: (chart, args, options) => {
        const {ctx} = chart;
        ctx.save();
        ctx.globalCompositeOperation = 'destination-over';
        ctx.fillStyle = options.color;
        ctx.fillRect(0, 0, chart.width, chart.height);
        ctx.restore();
    },
    defaults: {
        color: 'lightGreen'
    }
}