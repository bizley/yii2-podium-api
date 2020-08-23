# Yii 2 Podium API

**[Work In Progress]**

Sorry for neglecting this project for so long.

I'm rewriting it again because now I know more than 2 years ago. When I'll finish I'll know more than now but maybe another 
rewriting will not be necessary. We'll see.

The goal right now is simple - to make Podium easily extendable and for its components to be easy to implement.

I've decided that this package by default will use Active Records as its base since this is the default of Yii 2, but 
will be open to other implementations. As much as I love this framework I really need to fight with its solutions 
sometimes (but hey, Yii 3 is coming, right?). Anyway - if you find something terrible from the architectural point of 
view please let me know.

Podium is divided into the components (not to be mistaken by Yii's components although these are implemented as them) 
that take care of main aspects of forum structure. Each component is responsible for the actions (again, not Yii's 
actions) concerning its aspect, and these actions are implemented as services that operate on repositories. As for the 
repositories - these are objects that know about the storage of data they can handle and how to work with them. By 
default the storage here is a database mapped by the Active Records (as I said earlier), but it should be easy enough 
to implement custom storage (at least I'm trying to make it easy).

There are some rules:
 - only repositories know about the storage,
 - each repository knows how to handle one single storage unit and not more,
 - components operate on repositories, not on identificators.

So far the components ready are:
- [x] Category
- [x] Forum
- [x] Thread
- [x] Post
- [ ] Rank
- [ ] Poll
- [ ] Message
- [ ] Member
- [ ] Group
- [ ] Account

When API is ready, I'll start preparing the client.
