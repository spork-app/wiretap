import dayjs from "dayjs";

export default {
    actions: {
        track({ state, commit }, options) {
            axios.post(buildUrl('/wiretap/track'), {
                ...options,
                date: new Date(),
            })
                .then(response => {
                })
                .catch(error => {
                    console.log(error);
                });
        },
    },
};